<?php

namespace App\Controller;

use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
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
}
