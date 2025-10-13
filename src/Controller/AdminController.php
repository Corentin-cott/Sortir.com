<?php

namespace App\Controller;

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
    #[Route('/admin/import/participants', name: 'admin_import_participants')]
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

    #[Route('/admin//importdesactiver/{id}', name: 'admin_import_desactiver', requirements: ['id' => '\d+'], methods: ['POST'])]
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
            return $this->redirectToRoute('admin_import_participants');
        }

        $this->addFlash('error', 'Utilisateur deja inactif.');
        return $this->redirectToRoute('admin_import_participants');


    }

    #[Route('/admin/import/reactiver/{id}', name: 'admin_import_reactiver', requirements: ['id' => '\d+'], methods: ['POST'])]
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
            return $this->redirectToRoute('admin_import_participants');
        }

        $this->addFlash('error', 'Utilisateur deja inactif.');
        return $this->redirectToRoute('admin_import_participants');
    }


}