<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VillesFixtures extends Fixture
{
    public const VILLES_REFERENCE_PREFIX = 'villes-';

    public const VILLES_NAMES = [
    'Bordeaux',
    'Niort',
    'Nantes',
    'Paris',
    'Rennes',
    'Lyon'
];
    public const VILLES_CODEPOSTAL = [
        '33000',
        '79000',
        '44000',
        '75000',
        '35000',
        '69000',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::VILLES_NAMES as $key => $name) {
            $ville = new Ville();
            $ville->setNom($name);
            $ville->setCodePostal(self::VILLES_CODEPOSTAL[$key]);
            $manager->persist($ville);
            $this->addReference(self::VILLES_REFERENCE_PREFIX.$key, $ville);
        }
        $manager->flush();
    }
}
