<?php

namespace App\Services;

use App\Entity\Etat;
use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class SoftDeleteSorties
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger){
        $this->logger = $logger;

    }

    public function softDelete(Participant $organisateur, EntityManagerInterface $entityManager): void
    {
        $this->logger->info("SoftDelete sorties for organisateur {$organisateur->getId()}");
        $etatRepo = $entityManager->getRepository(Etat::class);
        foreach ($organisateur->getSortiesOrg() as $sortie) {
            if (method_exists($sortie, 'getDeletedAt') && $sortie->getDeletedAt() === null) {
                $sortie->setEtat($etatRepo->findOneBy(['libelle' => 'Archivée']));
                $entityManager->remove($sortie);
                $entityManager->flush();

            }
        }
    }
}