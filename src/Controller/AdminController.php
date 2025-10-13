<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Entity\Participant;
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

    #[Route('/admin/ajouter/lieu', name: 'admin_ajouter_lieu')]
    #[IsGranted("ROLE_ADMIN")]
    public function addLieu(Request $request, EntityManagerInterface $em): Response
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lieu);
            $em->flush();

            $this->addFlash('success', 'Lieu ajouté avec succès !');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/lieu/creer_modifier.html.twig', [
            'form' => $form,
        ]);
    }

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