<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Services\MeteoService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Services\FileManager;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


final class SortieController extends AbstractController
{
    private LoggerInterface $logger;
    public function __construct(LoggerInterface $logger){
        $this->logger = $logger;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/sortie/{id}', name: 'app_sortie_details', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function details(Request $request, EntityManagerInterface $em, Sortie $sortie, MeteoService $meteoService): Response
    {
        $siteOrga = $em->getRepository(Site::class)->find($sortie->getOrganisateur()->getSite());
        $lieu  = $em->getRepository(Lieu::class)->find($sortie->getLieu());
        $lieuVille  = $lieu->getVille();

        $weatherData = null;

        if ($lieu->getLatitude() && $lieu->getLongitude()) {
            $date = $sortie->getDateHeureDebut();
            $lat = $lieu->getLatitude();
            $lon = $lieu->getLongitude();

            $weatherData = $meteoService->getForecast($lat, $lon, $date);

            if ($weatherData !== null) {
                $weatherData['description'] = $meteoService->interpretWeatherCode($weatherData['weather_code']);
            }

        }
        // Envoie de la sortie au template
        return $this->render('sorties/afficher.html.twig', [
            'sortie' => $sortie,
            'siteOrga' => $siteOrga,
            'lieu' => $lieu,
            'lieuVille' => $lieuVille,
            'weather' => $weatherData,
        ]);
    }

    #[Route('/sortie/{id}/ajouter/commentaire', name: 'app_sortie_ajouter_commentaire', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function ajouterCommentaire(Request $request, EntityManagerInterface $em, Sortie $sortie): Response
    {
        // Création du nouveau commentaire
        $commentaire = new Commentaire();
        $user = $this->getUser();
        $participant = $em->getRepository(Participant::class)->find($user->getId());
        $commentaire->setParticipant($participant);
        $commentaire->setSortie($sortie);
        $commentaire->setCommentaire($request->request->get('contenu'));
        $commentaire->setDatePublication();

        // Sauvegarde en base
        $em->persist($commentaire);
        $em->flush();

        // Redirection sur la route
        return $this->redirectToRoute('app_sortie_details', ['id' => $sortie->getId()]);
    }

    #[Route('/sortie/{sortieId}/supprimer/commentaire/{commentaireId}', name: 'app_sortie_supprimer_commentaire', requirements: ['sortieId' => '\d+', 'commentaireId' => '\d+'], methods: ['POST'])]
    public function supprimerCommentaire(Request $request, EntityManagerInterface $em, int $sortieId, int $commentaireId): Response {
        $commentaire = $em->getRepository(Commentaire::class)->find($commentaireId);

        if (!$this->isCsrfTokenValid('supprimer_commentaire_' . $commentaireId, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $user = $this->getUser();
        if (
            $user !== $commentaire->getParticipant()
            && $user !== $commentaire->getSortie()->getOrganisateur()
            && !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException('Action non autorisée.');
        }

        $em->remove($commentaire);
        $em->flush();

        return $this->redirectToRoute('app_sortie_details', ['id' => $sortieId]);
    }

    #[Route('/sortie/creer', name: 'app_sortie_creer')]
    #[IsGranted('SORTIE_CREATE', message: 'Vous n\'êtes pas autorisé à voir cette page.')]
    public function creer(Request $request, EntityManagerInterface $em, FileManager $fileManager): Response
    {
        $user = $this->getUser();
        $organisateur = $em->getRepository(Participant::class)->find($user->getId());
        // Récupération de la liste des lieux pour afficher rue, ville, ect
        $lieux = $em->getRepository(Lieu::class)->findAll();
        $lieux = array_map(function($lieu) {
            return [
                'id' => $lieu->getId(),
                'rue' => $lieu->getRue(),
                'ville' => $lieu->getVille()->getNom(),
                'codePostal' => $lieu->getVille()->getCodePostal(),
                'latitude' => $lieu->getLatitude(),
                'longitude' => $lieu->getLongitude()
            ];
        }, $lieux);

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //gestion photo
            $photoFile = $form->get('photo')->getData();
            if ($photoFile instanceof UploadedFile) {
                if($name = $fileManager->upload($photoFile, 'uploads/backdrops/', $sortie->getId())) {
                    $sortie->setPhoto($name);
                }
            }

            $action = $request->request->get('action');
            if ($action === 'save') {
                $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Créée']);
            } elseif ($action === 'publish') {
                $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']);
            } else { // On sait jamais
                $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Créée']);
            }

            $sortie->setOrganisateur($organisateur);
            $sortie->setSiteOrg($organisateur->getSite());
            $sortie->setEtat($etat);

            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'La sortie a été créée avec succès !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('sorties/creer_modifier.html.twig', [
            'form' => $form->createView(),
            'lieux' => $lieux
        ]);
    }

    #[Route('/sortie/annuler/{id}', name: 'app_sortie_annuler', requirements: ['id' => '\d+'])]
    #[IsGranted('SORTIE_WITHDRAW', subject: 'sortie', message:'Vous n\'avez pas les droits pour annuler une sortie.')]
    public function annuler(Request $request, EntityManagerInterface $em, Sortie $sortie): Response
    {
        $siteOrga = $em->getRepository(Site::class)->find($sortie->getOrganisateur()->getSite());
        $lieu  = $em->getRepository(Lieu::class)->find($sortie->getLieu());
        $lieuVille  = $lieu->getVille();

        if ($request->isMethod('POST')) {
            $motif = $request->request->get('motif');

            if ($motif != null) {
                $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Annulée']);
                $sortie->setEtat($etat);
                $sortie->setAnnulationMotif($motif);

                $em->flush();

                $this->addFlash('success', 'La sortie a été annulée avec succès.');
                return $this->redirectToRoute('app_home');
            }
        }
        // Envoie de la sortie au template
        return $this->render('sorties/annuler.html.twig', [
            'sortie' => $sortie,
            'siteOrga' => $siteOrga,
            'lieu' => $lieu,
            'lieuVille' => $lieuVille
        ]);
    }

    #[Route('/sortie/modifier/{id}', name: 'app_sortie_modifier', requirements: ['id' => '\d+'])]
    #[IsGranted('SORTIE_EDIT', subject:'sortie', message: "Vous n'avez pas les droits pour modifier une sortie.")]
    public function modifier(Request $request, EntityManagerInterface $em, Sortie $sortie, FileManager $fileManager): Response
    {
        // Récupération de la liste des lieux pour le formulaire
        $lieux = $em->getRepository(Lieu::class)->findAll();
        $lieux = array_map(function($lieu) {
            return [
                'id' => $lieu->getId(),
                'rue' => $lieu->getRue(),
                'ville' => $lieu->getVille()->getNom(),
                'codePostal' => $lieu->getVille()->getCodePostal(),
                'latitude' => $lieu->getLatitude(),
                'longitude' => $lieu->getLongitude()
            ];
        }, $lieux);

        // Création du formulaire pour la sortie existante
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();
            if($photoFile instanceof UploadedFile) {
                //supprime l'ancien fichier
                if($sortie->getPhoto()) {
                    $fileManager->remove('uploads/backdrops/' . $sortie->getPhoto(), $sortie->getId());
                }
                //Fait l'enregistrement du nouveau fichier
                if($name = $fileManager->upload($photoFile, 'uploads/backdrops/', $sortie->getId())) {
                    $sortie->setPhoto($name);
                }
            }

            $action = $request->request->get('action');
            if ($action === 'save') {
                $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Créée']);
            } elseif ($action === 'publish') {
                $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']);
            }

            $sortie->setEtat($etat);
            $em->flush();

            $this->addFlash('success', 'La sortie a été modifiée avec succès !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('sorties/creer_modifier.html.twig', [
            'form' => $form->createView(),
            'lieux' => $lieux,
            'sortie' => $sortie
        ]);
    }
}
