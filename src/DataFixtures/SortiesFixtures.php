<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortiesFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $sortie1 = new Sortie();
        $sortie1->setNom("Mardi apéro Niortais");
        $sortie1->setDateHeureDebut(new \DateTimeImmutable("2025-10-25T20:00"));
        $sortie1->setDuree(90);
        $sortie1->setDateLimiteInscription(new \DateTimeImmutable("2025-10-20T20:00"));
        $sortie1->setNbInscriptionMax(10);
        $sortie1->setDescriptionInfos("Soirée d'intégration...");
        $sortie1->setEtat($this->getReference("etat-1", Etat::class));
        $sortie1->setOrganisateur($this->getReference("participant1", Participant::class));
        $sortie1->setSiteOrg($this->getReference("site-1", Site::class));
        $sortie1->setLieu($this->getReference("lieu-a", Lieu::class));
        $manager->persist($sortie1);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ParticipantFixtures::class,
            SiteFixtures::class,
            EtatFixtures::class,
            LieuFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['sorties'];
    }
}
