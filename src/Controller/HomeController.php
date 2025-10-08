<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request,
                          SortieRepository $sortieRepository,
                          SiteRepository $siteRepository
    ): Response {
        // Récupération des filtres
        $siteId = $request->query->get('site');
        $search = $request->query->get('search');
        $dateDebut = $request->query->get('dateDebut');
        $dateFin = $request->query->get('dateFin');
        $filtreOrganisateur = $request->query->getBoolean('organisateur');
        $filtreInscrit = $request->query->getBoolean('inscrit');
        $filtreNonInscrit = $request->query->getBoolean('nonInscrit');
        $filtrePassees = $request->query->getBoolean('passees');

        $sorties = $sortieRepository->findWithFilters(
            $siteId,
            $search,
            $dateDebut,
            $dateFin,
            $this->getUser(),
            $filtreOrganisateur,
            $filtreInscrit,
            $filtreNonInscrit,
            $filtrePassees
        );

        return $this->render('home/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $siteRepository->findAll(),
            'user' => $this->getUser(),
            'today' => new \DateTime(),
        ]);
    }
    #[Route('/sinscrire/{id}', name: 'app_home_sinscrire', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function sinscrire(EntityManagerInterface $entityManager, int  $id): Response
    {
        $sortie = $entityManager->find(Sortie::class, $id);
        if (!$sortie) {
            throw $this->createNotFoundException('Sortie inexistante');

        }
        $participant = $this->getUser();
        if (!$participant) {
            throw $this->createNotFoundException('Connectez vous pour pouvoir vous inscrire');

        }
        $participant = $entityManager->getRepository(Participant::class)->find($participant->getId());

        $nbMax = $sortie->getNbInscriptionMax();
        $nbInscrit = count($sortie->getParticipants());

        //User =/= organisateur
        if($participant->getId() == $sortie->getOrganisateur()->getId()){
            $this->addFlash('error', 'Vous ne pouvez pas vous inscrire à la sortie en tant qu \' organisateur !');
            return $this->redirectToRoute('app_home');
        }

        //date limite
        if ($sortie->getDateLimiteInscription() < new \DateTime()) {
            $this->addFlash('error', 'La date limite d’inscription est dépassée.');
            return $this->redirectToRoute('app_home');
        }
        // nb Max
        if ($nbInscrit == $nbMax) {
            $this->addFlash('error', 'La sortie est complète.');
            return $this->redirectToRoute('app_home');
        }

        $sortie->sinscrire($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();
        $this->addFlash('success', 'Inscription réussie !');
        return $this->redirectToRoute('app_home');

    }
    #[Route('/desinscrire/{id}', name: 'app_home_desinscrire', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function desinscrire(EntityManagerInterface $entityManager, int  $id): Response
    {
        $sortie = $entityManager->find(Sortie::class, $id);
        if (!$sortie) {
            throw $this->createNotFoundException('Sortie inexistante');

        }
        $participant = $this->getUser();
        if (!$participant) {
            throw $this->createNotFoundException('Connectez vous pour pouvoir vous inscrire');

        }
        $participant = $entityManager->getRepository(Participant::class)->find($participant->getId());


        //User =/= participants
        if(!$sortie->estInscrit($participant)){
            $this->addFlash('error', 'Vous n\êtes pas inscrit !');
            return $this->redirectToRoute('app_home');
        }

        $sortie->desinscrire($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Desinscription réussie !');
        return $this->redirectToRoute('app_home');

    }

}
