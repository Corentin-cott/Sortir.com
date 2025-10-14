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

    #[Route('/admin/gestion/supprimer/lieu/{id}', name: 'admin_lieu_supprimer', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function supprimerLieu(Lieu $lieu, EntityManagerInterface $em): Response
    {
        // Suppression du lieu
        $em->remove($lieu);
        $em->flush();

        $this->addFlash('success', 'Lieu supprimé avec succès !');
        return $this->redirectToRoute('admin_gestion_lieux');
    }

    #[Route('/admin/gestion/modifier/lieu/{id}', name: 'admin_lieu_modifier', methods: ['POST', 'GET'])]
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
    #[Route('/admin/gestion/villes', name: 'admin_gestion_villes')]
    #[IsGranted("ROLE_ADMIN")]
    public function gestionVille(Request $request, EntityManagerInterface $em): Response
    {
        // Pour le tableau des villes
        $listeVilles = $em->getRepository(Ville::class)->findAll();

        // Pour le formulaire d'ajout de villes
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Le formulaire à été rempli
            $em->persist($ville);
            $em->flush();

            $this->addFlash('success', 'Ville ajouté avec succès !');
            return $this->redirectToRoute('admin_gestion_villes');
        }

        return $this->render('admin/ville/gestionVilles.html.twig', [
            'form' => $form,
            'listeVilles' => $listeVilles,
            'ville' => $ville,
        ]);
    }

    #[Route('/admin/gestion/supprimer/ville/{id}', name: 'admin_ville_supprimer', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function supprimerVille(Ville $ville, EntityManagerInterface $em): Response
    {
        // Suppression du lieu
        $em->remove($ville);
        $em->flush();

        $this->addFlash('success', 'Ville supprimé avec succès !');
        return $this->redirectToRoute('admin_gestion_villes');
    }

    #[Route('/admin/gestion/modifier/ville/{id}', name: 'admin_ville_modifier', methods: ['POST', 'GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function modifierVille(EntityManagerInterface $em, Request $request, int $id): Response
    {
        // Pour le tableau des villes
        $listeVilles = $em->getRepository(Ville::class)->findAll();

        // Modification du lieu
        $ville = $em->getRepository(Ville::class)->find($id);
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ville);
            $em->flush();
            $this->addFlash('success', 'Ville modifié avec succès !');
            return $this->redirectToRoute('admin_gestion_villes');
        }

        return $this->render('admin/ville/gestionVilles.html.twig', [
            'form' => $form,
            'listeVilles' => $listeVilles,
            'ville' => $ville,
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