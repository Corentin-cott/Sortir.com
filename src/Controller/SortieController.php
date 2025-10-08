<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/sortie/creer', name: 'app_sortie_creer')]
    public function creer(Request $request, EntityManagerInterface $em): Response
    {
        // Vérification que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');
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
            $action = $request->request->get('action');
            if ($action === 'save') {
                $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Crée']);
            } elseif ($action === 'publish') {
                $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']);
            } else { // On sait jamais
                $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Crée']);
            }

            $sortie->setOrganisateur($organisateur);
            $sortie->setSiteOrg($organisateur->getSite());
            $sortie->setEtat($etat);

            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'Sortie créer avec succès !');
        }

        return $this->render('sorties/creer.html.twig', [
            'form' => $form->createView(),
            'lieux' => $lieux
        ]);
    }

    #[Route('/sortie/annuler/{id}', name: 'app_sortie_annuler', requirements: ['id' => '\d+'])]
    public function annuler(Request $request, EntityManagerInterface $em, int $id): Response
    {
        // Vérification que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupération des infos de la sortie à partir de l'id
        $sortie = $em->getRepository(Sortie::class)->find($id);
        $siteOrga = $em->getRepository(Site::class)->find($sortie->getOrganisateur()->getSite());
        $lieu  = $em->getRepository(Lieu::class)->find($sortie->getLieu());
        $lieuVille  = $lieu->getVille();

        // Vérification que l'organisateur est bien l'utilisateur demandant l'annulation
        $organisateur = $em->getRepository(Participant::class)->find($this->getUser()->getId());
        if ($sortie->getOrganisateur()->getId() != $organisateur->getId()) {
            $this->addFlash('error', 'Cette sortie ne vous appartient pas !');
            return $this->redirectToRoute('app_home');
        }

        // Envoie de la sortie au template
        return $this->render('sorties/annuler.html.twig', [
            'sortie' => $sortie,
            'siteOrga' => $siteOrga,
            'lieu' => $lieu,
            'lieuVille' => $lieuVille
        ]);
    }
}
