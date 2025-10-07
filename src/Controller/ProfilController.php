<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilController extends AbstractController
{
    #[Route('/edit', name: 'app_edit')]
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        // temporaire : on charge un utilisateur pour le dev
        $user = $this->getUser();
        $user = $em->getRepository(Participant::class)->find($user->getId());

        if (!$user) {
            return new Response('Utilisateur non connecté (auth à venir)');
        }

        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // gérer le mot de passe si rempli
            if ($form->get('password')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
                $user->setPassword($hashedPassword);
            }

//            // gérer la photo si uploadée
//            $photo = $form->get('photo')->getData();
//            if ($photo) {
//                $newFilename = uniqid().'.'.$photo->guessExtension();
//                $photo->move($this->getParameter('photo_directory'), $newFilename);
//                $user->setPhoto($newFilename);
//            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('profil/edit.html.twig', [
            'profilForm' => $form->createView(),
        ]);
    }
}
