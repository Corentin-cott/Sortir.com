<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public const ETATS_REFERENCE_PREFIX = 'etat-';

    public const ETATS = [
        "Créée",
        "Ouverte",
        "Cloturée",
        "Activitée en cours",
        "Passée",
        "Annulée"
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ETATS as $key => $value) {
            $etat = new Etat();
            $etat->setLibelle($value);
            $manager->persist($etat);
            $this->addReference(self::ETATS_REFERENCE_PREFIX . $key, $etat);
        }
        $manager->flush();
    }
}
