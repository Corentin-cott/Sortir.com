<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class SortieController extends AbstractController
{
    private LoggerInterface $logger;
    public function __construct(LoggerInterface $logger){
        $this->logger = $logger;
    }

    #[Route('/sortie/{id}', name: 'app_sortie_details', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function details(Request $request, EntityManagerInterface $em, int $id): Response
    {
        // Récupération des infos de la sortie à partir de l'id
        $sortie = $em->getRepository(Sortie::class)->find($id);
        $siteOrga = $em->getRepository(Site::class)->find($sortie->getOrganisateur()->getSite());
        $lieu  = $em->getRepository(Lieu::class)->find($sortie->getLieu());
        $lieuVille  = $lieu->getVille();

        // Envoie de la sortie au template
        return $this->render('sorties/afficher.html.twig', [
            'sortie' => $sortie,
            'siteOrga' => $siteOrga,
            'lieu' => $lieu,
            'lieuVille' => $lieuVille
        ]);

    }

    #[Route('/sortie/creer', name: 'app_sortie_creer')]
    public function creer(Request $request, EntityManagerInterface $em): Response
    {
        // Vérification que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        $organisateur = $em->getRepository(Participant::class)->find($user->getId());

        if( !$organisateur->isActif()){
            $this->addFlash('danger', 'Votre compte est désactivé vous ne pouvez pas effectuer cette action');
            return $this->redirectToRoute('app_home');
        }
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

            $this->addFlash('success', 'Sortie créér avec succès !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('sorties/creer_modifier.html.twig', [
            'form' => $form->createView(),
            'lieux' => $lieux
        ]);
    }

    #[Route('/sortie/annuler/{id}', name: 'app_sortie_annuler', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function annuler(Request $request, EntityManagerInterface $em, int $id): Response
    {
        // Vérification que l'utilisateur est connecté
//        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupération des infos de la sortie à partir de l'id
        $sortie = $em->getRepository(Sortie::class)->find($id);
        $siteOrga = $em->getRepository(Site::class)->find($sortie->getOrganisateur()->getSite());
        $lieu  = $em->getRepository(Lieu::class)->find($sortie->getLieu());
        $lieuVille  = $lieu->getVille();

        // Vérification que l'organisateur est bien l'utilisateur demandant l'annulation
        $user = $em->getRepository(Participant::class)->find($this->getUser());
        $this->logger->info("[SORTIE CONTROLLER] ----- Organisateur : {$sortie->getOrganisateur()->getId()}");
        $this->logger->info("[SORTIE CONTROLLER] ----- User : {$user->getId()}");
        $this->logger->info("[SORTIE CONTROLLER] ----- User est admin ? {$user->isAdmin()}");

        if (!$user->isAdmin() && ($user !== $sortie->getOrganisateur())) {
            $this->addFlash('danger', 'Cette sortie ne vous appartient pas !');
            return $this->redirectToRoute('app_home');
        }

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
    public function modifier(Request $request, EntityManagerInterface $em, int $id): Response
    {
        // Vérification que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupération de l'utilisateur & organisateur
        $user = $this->getUser();
        $organisateur = $em->getRepository(Participant::class)->find($user->getId());

        // Récupération de la sortie à modifier
        $sortie = $em->getRepository(Sortie::class)->find($id);
        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée.');
        }

        // Vérification que l'utilisateur est bien l'organisateur
        if ($sortie->getOrganisateur() !== $organisateur) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres sorties.');
        }

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
            $action = $request->request->get('action');
            if ($action === 'save') {
                $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Créée']);
            } elseif ($action === 'publish') {
                $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']);
            }

            $sortie->setEtat($etat);
            $em->flush();

            $this->addFlash('success', 'Sortie modifiée avec succès !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('sorties/creer_modifier.html.twig', [
            'form' => $form->createView(),
            'lieux' => $lieux,
            'sortie' => $sortie
        ]);
    }
}
