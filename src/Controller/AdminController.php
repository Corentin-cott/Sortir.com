<?php

namespace App\Controller;

use App\Services\ParticipantImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController {
    #[Route('/admin/import/participants', name: 'admin_import_participants')]
    public function importParticipants(Request $request, ParticipantImporter $importer): Response
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

        return $this->render('admin/dashboard.html.twig');
    }
}