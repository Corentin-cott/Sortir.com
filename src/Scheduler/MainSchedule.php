<?php

namespace App\Scheduler;


use App\Message\UpdateEtatSortieMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;


#[AsSchedule('hello')]
final class MainSchedule implements ScheduleProviderInterface
{
        public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(RecurringMessage::every('1 minutes', new UpdateEtatSortieMessage()));

    }
}
