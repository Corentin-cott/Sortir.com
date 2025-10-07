<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{
    public const SITE_REFERENCE = 'site-';

    public const SITE = [
          'Saint Herblain',
          'Niort',
        'Quimper',
        'Nantes,'

    ];

    public function load(ObjectManager $manager): void
    {
       foreach (self::SITE as $key => $value) {
           $site = new Site();
           $site->setNom($value);
           $manager->persist($site);
           $this->addReference(self::SITE_REFERENCE . $key, $site);
       }

        $manager->flush();
    }
}
