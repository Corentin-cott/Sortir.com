<?php

namespace App\Scheduler;

use App\Entity\Etat;
use App\Message\UpdateEtatSortieMessage;
use App\Services\EtatSortieUpdate;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateEtatSortieMessageHandler
{
    public function __construct(
        private EtatSortieUpdate $etatSortieUpdate,
        private LoggerInterface $logger,
    ){}

    public function __invoke(UpdateEtatSortieMessage $message){
        $this->logger->info("Mise à jour des états lancée par le scheduler.");
        $this->etatSortieUpdate->updateEtats();
    }
}