<?php

namespace App\Scheduler;


use App\Services\EtatSortieUpdate;

use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;


#[AsPeriodicTask(frequency: '5 minutes')]
class MiseAjourPlanifieeSortie
{
    public function __construct(private EtatSortieUpdate $etatSortieUpdate){}

    public function __invoke():void
    {
        $this->etatSortieUpdate->updateEtats();
        echo("Mise à jour des états effectuée sur les sorties.");

    }
}