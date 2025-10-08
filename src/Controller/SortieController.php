<?php

namespace App\Controller;

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
        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'Sortie créer avec succès !');
        }

        return $this->render('sorties/creer.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
