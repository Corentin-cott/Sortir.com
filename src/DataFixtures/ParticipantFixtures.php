<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Participant 1 : John Doe
        $participant1 = new Participant();
        $participant1->setNom('Doe');
        $participant1->setPrenom('John');
        $participant1->setPseudo('doedoe');
        $participant1->setEmail('john.doe@example.com');
        $participant1->setTelephone('0601010101');
        $participant1->setSite($this->getReference('site1'));
        $participant1->setPassword($this->passwordHasher->hashPassword($participant1, 'myPassword'));
        $this->addReference('participant1', $participant1);
        $manager->persist($participant1);

        // Participant 2 : Jane Smith
        $participant2 = new Participant();
        $participant2->setNom('Smith');
        $participant2->setPrenom('Jane');
        $participant2->setPseudo('janesmith');
        $participant2->setEmail('jane.smith@example.com');
        $participant2->setTelephone('0602020202');
        $participant2->setSite($this->getReference('site2'));
        $participant2->setPassword($this->passwordHasher->hashPassword($participant2, 'securePass1'));
        $this->addReference('participant2', $participant2);
        $manager->persist($participant2);

        // Participant 3 : Alice Martin
        $participant3 = new Participant();
        $participant3->setNom('Martin');
        $participant3->setPrenom('Alice');
        $participant3->setPseudo('alicemartin');
        $participant3->setEmail('alice.martin@example.com');
        $participant3->setTelephone('0603030303');
        $participant3->setSite($this->getReference('site1'));
        $participant3->setPassword($this->passwordHasher->hashPassword($participant3, 'securePass2'));
        $this->addReference('participant3', $participant3);
        $manager->persist($participant3);

        // Participant 4 : Bob Dupont
        $participant4 = new Participant();
        $participant4->setNom('Dupont');
        $participant4->setPrenom('Bob');
        $participant4->setPseudo('bobdupont');
        $participant4->setEmail('bob.dupont@example.com');
        $participant4->setTelephone('0604040404');
        $participant4->setSite($this->getReference('site2'));
        $participant4->setPassword($this->passwordHasher->hashPassword($participant4, 'securePass3'));
        $this->addReference('participant4', $participant4);
        $manager->persist($participant4);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SiteFixtures::class,
        ];
    }
}
