<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Entity\Participant;
use App\Form\VilleType;
use App\Services\ParticipantImporter;
use App\Services\SoftDeleteSorties;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function PHPUnit\Framework\throwException;

final class AdminController extends AbstractController {
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    #[IsGranted("ROLE_ADMIN")]
    public function importParticipants(Request $request, ParticipantImporter $importer, EntityManagerInterface $em): Response
    {
        $utilisateurs = $em->getRepository(Participant::class)->findAll();
        if ($request->isMethod('POST')) {
            $file = $request->files->get('csv_file');
            if ($file) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/var/imports/';
                $filePath = $uploadDir . $file->getClientOriginalName();

                try {
                    $file->move($uploadDir, $file->getClientOriginalName());
                    $result = $importer->importFromCsv($filePath);
                    return $this->render('admin/dashboard.html.twig', ['result' => $result,
                            'utilisateurs' => $utilisateurs  ]
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du fichier.');
                }
            }
        }
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

        return $this->render('admin/ville/creer_modifier.html.twig', [
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

    #[Route('admin/grant/{id}', name:'admin_grant', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function grantedAdmin(Participant $utilisateur, Request $request, EntityManagerInterface $em): Response
    {
       $token = $request->request->get('_token');
       if (!$this->isCsrfTokenValid('grant'.$utilisateur->getId(), $token)) {
           throw $this->createAccessDeniedException("Action non autorisée (token invalide).");
       }

       $utilisateur->setRoles(['ROLE_ADMIN']);
       $em->flush();
       $this->addFlash('success', 'Utilisateur promu Admin.');

       return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('admin/demote/{id}', name:'admin_demote', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function demote(Participant $utilisateur, Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('demote'.$utilisateur->getId(), $token)) {
            throw $this->createAccessDeniedException("Action non autorisée (token invalide).");
        }
        $utilisateur->setRoles([]);
        $em->flush();
        $this->addFlash('success', 'Utilisateur rétrogradé avec succès.');

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/delete/participant/{id}', name:'admin_delete_participant', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteParticipant(Participant $utilisateur, Request $request, EntityManagerInterface $em, SoftDeleteSorties $deleteSorties): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_participant'.$utilisateur->getId(), $token)) {
            throw $this->createAccessDeniedException("Action non autorisée (token invalide).");
        }
        $deleteSorties->softDelete($utilisateur, $em);
        $em->remove($utilisateur);
        $em->flush();
        $this->addFlash('success', "L'utilisateur a bien été supprimée");
        return $this->redirectToRoute('admin_dashboard');
    }


}