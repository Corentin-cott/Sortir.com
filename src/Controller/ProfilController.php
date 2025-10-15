<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfilController extends AbstractController
{
    #[Route('/edit/{id}', name: 'app_edit', requirements: ['id'=>'\d+'])]
    #[IsGranted("ROLE_USER")]
    public function edit(
        Participant $participant,
        Security $security,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger
    ): Response
    {
        // récupérer l'utilisateur courant
        $user = $this->getUser();
        $user = $em->getRepository(Participant::class)->find($user->getId());

        $isOwner = $user === $participant;
        $form = $this->createForm(ProfilType::class, $user, [
            'attr' => ['enctype' => 'multipart/form-data'] // nécessaire pour upload
        ]);
        $form->handleRequest($request);

        if($isOwner){
            if ($form->isSubmitted() && $form->isValid()) {
                // gérer le mot de passe si rempli
                if ($form->get('password')->getData()) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
                    $user->setPassword($hashedPassword);
                }

                // gérer la photo si uploadée
                $photoFile = $form->get('photo')->getData();
                if ($photoFile) {
                    $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                    try {
                        $photoFile->move(
                            $this->getParameter('uploads_directory'), // défini dans services.yaml
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de la photo.');
                    }

                    $user->setPhoto($newFilename);
                }

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Profil mis à jour avec succès !');
                return $this->redirectToRoute('app_home');
            }
        }
        return $this->render('profil/edit.html.twig', [
            'participant' => $participant,
            'isOwner' => $isOwner,
            'profilForm' => $form->createView(),
        ]);
    }

}
