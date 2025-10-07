<?php
// src/Controller/SortieController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\SortieRepository;
use App\Repository\SiteRepository;

class SortieController extends AbstractController
{
    #[Route('/sorties', name: 'sorties_index')]
    public function index(SortieRepository $sortieRepo, SiteRepository $siteRepo): Response
    {
        // Récupère toutes les sorties
        $sorties = $sortieRepo->findAll();

        // Récupère tous les sites
        $sites = $siteRepo->findAll();

        // On récupère juste les noms des sites pour le filtre
        $siteNames = array_map(fn($s) => $s->getNom(), $sites);

        return $this->render('sorties/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $siteNames,
        ]);
    }
}
