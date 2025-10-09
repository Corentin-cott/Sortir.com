<?php

namespace App\Services;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

class EtatSortieUpdate
{
    public function __construct( private SortieRepository $sortieRepository, private EntityManagerInterface $entityManager){}

    public function updateEtats() :void{
        $now = new \DateTimeImmutable();
        $sorties = $this->sortieRepository->findAll();

        foreach($sorties as $sortie){
            $this->miseAJourEtat($sortie, $now);
        }
       $this->entityManager->flush();
    }

    public function miseAJourEtat(Sortie $sortie, \DateTimeImmutable $now): void
    {
        $etatActuel = $sortie->getEtat()->getLibelle();
        $dateDebut = $sortie->getDateHeureDebut();
        $dateFin = $dateDebut->modify("+ {$sortie->getDuree()} minutes");
        $dateLimite = $sortie->getDateLimiteInscription();
//        $dateArchive = $dateDebut->modify("+ 1 month");

        $nbInscrits = count($sortie->getParticipants());
        $nbMax = $sortie->getNbInscriptionMax();

        $etatRepo = $this->entityManager->getRepository(Etat::Class);

        if($etatActuel === 'Ouverte' && $now > $dateLimite || $nbInscrits == $nbMax){
            $sortie->setEtat($etatRepo->findOneBy(['libelle' => 'Cloturée']));
        }
        if($etatActuel === 'Cloturée' && $now >= $dateDebut && $now <= $dateFin){
            $sortie->setEtat($etatRepo->findOneBy(['libelle' => 'Activitée en cours']));
        }

        if($etatActuel === 'En cours' && $now > $dateFin){
            $sortie->setEtat($etatRepo->findOneBy(['libelle' => 'Passée']));
        }

//        if($etatActuel === 'Terminée' && $now > $dateArchive || $etatActuel === 'Annuleé' && $now > $dateArchive){
//            $sortie->setEtat($etatRepo->findOneBy(['libelle' => 'Archivée']));
//        }
    }

}