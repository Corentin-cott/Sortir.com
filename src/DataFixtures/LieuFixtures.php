<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $lieu1 = new Lieu();
        $lieu1->setNom('Le Bar du Port');
        $lieu1->setRue('12 Quai Richelieu');
        $lieu1->setLatitude(44.8412);
        $lieu1->setLongitude(-0.5800);
        $lieu1->setVille($this->getReference('villes-0', Ville::class));
        $this->addReference('lieu-1', $lieu1);
        $manager->persist($lieu1);

        $lieu2 = new Lieu();
        $lieu2->setNom('Café de la Bourse');
        $lieu2->setRue('5 Place de la Bourse');
        $lieu2->setLatitude(44.8400);
        $lieu2->setLongitude(-0.5740);
        $lieu2->setVille($this->getReference('villes-0', Ville::class));
        $this->addReference('lieu-2', $lieu2);
        $manager->persist($lieu2);

        // Lieux Niort
        $lieuA = new Lieu();
        $lieuA->setNom('Le Temple Bar');
        $lieuA->setRue("4 Esplanade de la République");
        $lieuA->setLongitude(46.3258);
        $lieuA->setLatitude(-0.4606);
        $lieuA->setVille($this->getReference('villes-1', Ville::class));
        $this->addReference('lieu-a', $lieuA);
        $manager->persist($lieuA);

        $lieu3 = new Lieu();
        $lieu3->setNom('La Guinguette');
        $lieu3->setRue('8 Rue Saint-Jean');
        $lieu3->setLatitude(46.3241);
        $lieu3->setLongitude(-0.4582);
        $lieu3->setVille($this->getReference('villes-1',Ville::class));
        $this->addReference('lieu-3', $lieu3);
        $manager->persist($lieu3);

        $lieu4 = new Lieu();
        $lieu4->setNom('Le Comptoir Niortais');
        $lieu4->setRue('14 Rue de l’Horloge');
        $lieu4->setLatitude(46.3235);
        $lieu4->setLongitude(-0.4608);
        $lieu4->setVille($this->getReference('villes-1',Ville::class));
        $this->addReference('lieu-4', $lieu4);
        $manager->persist($lieu4);

        // Lieux Nantes
        $lieu5 = new Lieu();
        $lieu5->setNom('Le Lieu Unique');
        $lieu5->setRue('2 Quai Ferdinand Favre');
        $lieu5->setLatitude(47.2181);
        $lieu5->setLongitude(-1.5528);
        $lieu5->setVille($this->getReference('villes-2',Ville::class));
        $this->addReference('lieu-5', $lieu5);
        $manager->persist($lieu5);

        $lieu6 = new Lieu();
        $lieu6->setNom('Café de la Tour');
        $lieu6->setRue('6 Place du Commerce');
        $lieu6->setLatitude(47.2138);
        $lieu6->setLongitude(-1.5535);
        $lieu6->setVille($this->getReference('villes-2',Ville::class));
        $this->addReference('lieu-6', $lieu6);
        $manager->persist($lieu6);

        // Lieux Paris
        $lieu7 = new Lieu();
        $lieu7->setNom('Le Temple Bar Paris');
        $lieu7->setRue('4 Esplanade de la République');
        $lieu7->setLatitude(48.8566);
        $lieu7->setLongitude(2.3522);
        $lieu7->setVille($this->getReference('villes-3',Ville::class));
        $this->addReference('lieu-7', $lieu7);
        $manager->persist($lieu7);

        $lieu8 = new Lieu();
        $lieu8->setNom('Café Montmartre');
        $lieu8->setRue('12 Rue Lepic');
        $lieu8->setLatitude(48.8841);
        $lieu8->setLongitude(2.3385);
        $lieu8->setVille($this->getReference('villes-3',Ville::class));
        $this->addReference('lieu-8', $lieu8);
        $manager->persist($lieu8);

        // Lieu Rennes
        $lieu9 = new Lieu();
        $lieu9->setNom('Le Saint-Germain');
        $lieu9->setRue('10 Place Sainte-Anne');
        $lieu9->setLatitude(48.1147);
        $lieu9->setLongitude(-1.6805);
        $lieu9->setVille($this->getReference('villes-4',Ville::class));
        $this->addReference('lieu-9', $lieu9);
        $manager->persist($lieu9);

        // Lieu Lyon
        $lieu10 = new Lieu();
        $lieu10->setNom('La Terrasse Lyonnaise');
        $lieu10->setRue('3 Quai Saint-Vincent');
        $lieu10->setLatitude(45.7640);
        $lieu10->setLongitude(4.8357);
        $lieu10->setVille($this->getReference('villes-5',Ville::class));
        $this->addReference('lieu-10', $lieu10);
        $manager->persist($lieu10);

        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            VillesFixtures::class,
        ];
    }


}
