<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Entity\Participant;
use App\Form\VilleType;
use App\Services\ParticipantImporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminController extends AbstractController {
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    #[IsGranted("ROLE_ADMIN")]
    public function importParticipants(Request $request, ParticipantImporter $importer, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('csv_file');
            if ($file) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/var/imports/';
                $filePath = $uploadDir . $file->getClientOriginalName();

                try {
                    $file->move($uploadDir, $file->getClientOriginalName());
                    $result = $importer->importFromCsv($filePath);
                    return $this->render('admin/dashboard.html.twig', ['result' => $result]);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du fichier.');
                }
            }
        }
        $utilisateurs = $em->getRepository(Participant::class)->findAll();

        return $this->render('admin/dashboard.html.twig',
        ['utilisateurs' => $utilisateurs]);
    }

    /* ROUTE DES LIEUX */
    #[Route('/admin/gestion/lieux', name: 'admin_gestion_lieux')]
    #[IsGranted("ROLE_ADMIN")]
    public function gestionLieux(Request $request, EntityManagerInterface $em): Response
    {
        // Pour le tableau des lieux
        $listeLieux = $em->getRepository(Lieu::class)->findAll();

        // Pour le formulaire d'ajout de lieux
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Le formulaire à été rempli
            $em->persist($lieu);
            $em->flush();

            $this->addFlash('success', 'Lieu ajouté avec succès !');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/lieu/gestionLieux.html.twig', [
            'form' => $form,
            'listeLieux' => $listeLieux,
            'lieu' => $lieu,
        ]);
    }

    #[Route('/admin/gestion/supprimer/lieux/{id}', name: 'admin_lieu_supprimer', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function supprimerLieu(Lieu $lieu, EntityManagerInterface $em): Response
    {
        // Suppression du lieu
        $em->remove($lieu);
        $em->flush();

        $this->addFlash('success', 'Lieu supprimé avec succès !');
        return $this->redirectToRoute('admin_gestion_lieux');
    }

    #[Route('/admin/gestion/modifier/lieux/{id}', name: 'admin_lieu_modifier', methods: ['POST', 'GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function modifierLieu(EntityManagerInterface $em, Request $request, int $id): Response
    {
        // Pour le tableau des lieux
        $listeLieux = $em->getRepository(Lieu::class)->findAll();

        // Modification du lieu
        $lieu = $em->getRepository(Lieu::class)->find($id);
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lieu);
            $em->flush();

            $this->addFlash('success', 'Lieu modifié avec succès !');
            return $this->redirectToRoute('admin_gestion_lieux');
        }

        return $this->render('admin/lieu/gestionLieux.html.twig', [
            'form' => $form,
            'listeLieux' => $listeLieux,
            'lieu' => $lieu,
        ]);
    }
    /* FIN ROUTES DES LIEUX */
    /* ROUTES DES VILLES */
    #[Route('/admin/ajouter/ville', name: 'admin_ajouter_ville')]
    #[IsGranted("ROLE_ADMIN")]
    public function addVille(Request $request, EntityManagerInterface $em): Response
    {
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ville);
            $em->flush();

            $this->addFlash('success', 'Ville ajouté avec succès !');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/ville/gestionLieux.html.twig', [
            'form' => $form,
        ]);
    }
    /* FIN ROUTES DES VILLES */

    #[Route('/admin/desactiver/{id}', name: 'admin_desactiver', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function desactiver(Participant $utilisateur, Request $request, EntityManagerInterface $em): Response
    {

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('desactiver'.$utilisateur->getId(), $token)) {
            throw $this->createAccessDeniedException('Action non autorisée (token invalide).');
        }

        if($utilisateur->isActif()) {
            $utilisateur->setActif(false);
            $em->flush();
            $this->addFlash('success', 'Utilisateur désactivé.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $this->addFlash('error', 'Utilisateur deja inactif.');
        return $this->redirectToRoute('admin_dashboard');


    }

    #[Route('/admin/reactiver/{id}', name: 'admin_reactiver', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function reactiver(Participant $utilisateur, Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('reactiver'.$utilisateur->getId(), $token)) {
            throw $this->createAccessDeniedException('Action non autorisée (token invalide).');
        }

        if(!$utilisateur->isActif()){
            $utilisateur->setActif(true);
            $em->flush();
            $this->addFlash('success', 'Utilisateur Réactivé.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $this->addFlash('error', 'Utilisateur deja inactif.');
        return $this->redirectToRoute('admin_dashboard');
    }


}