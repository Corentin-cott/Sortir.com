<?php

namespace App\Scheduler;


use App\Services\EtatSortieUpdate;

use Psr\Log\LoggerInterface;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;


#[AsPeriodicTask(frequency: '1 minute')]
class MiseAjourPlanifieeSortie
{
    public function __construct(private EtatSortieUpdate $etatSortieUpdate, private LoggerInterface $logger){}

    public function __invoke():void
    {
        $this->logger->info("Mise à jour des états effectuée sur les sorties.");
        $this->etatSortieUpdate->updateEtats();

    }
}