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
    #[Route('/sortie/creer', name: 'app_sortie')]
    public function new(Request $request, EntityManagerInterface $em): Response
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
}
