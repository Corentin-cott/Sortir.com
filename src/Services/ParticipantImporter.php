<?php

namespace App\Services;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantImporter
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function importFromCsv(string $filePath): array
    {
        $results = [
            'imported' => 0,
            'totalToImport' => 0,
            'errors' => []
        ];

        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setDelimiter(';'); // définition du délimiteur
        $csv->setHeaderOffset(0); // Première ligne = nom des colonnes

        $stmt = Statement::create();
        $records = $stmt->process($csv);

        foreach ($records as $i => $record) {
            try {
                // Vérifie si le participant existe déjà
                if ($this->em->getRepository(Participant::class)->findOneBy(['email' => $record['email']])) {
                    $results['errors'][] = "Ligne $i : email déjà existant ({$record['email']})";
                    $results['totalToImport']++;
                    continue;
                }
                if ($this->em->getRepository(Participant::class)->findOneBy(['pseudo' => $record['pseudo']])) {
                    $results['errors'][] = "Ligne $i : pseudo déjà existant ({$record['pseudo']})";
                    $results['totalToImport']++;
                    continue;
                }

                // Vérifie si le site existe en recherchant son nom
                $site = $this->em->getRepository(Site::class)->findOneBy(['nom' => $record['site']]);
                if (!$site) {
                    $results['errors'][] = "Ligne $i : site inconnu ({$record['site']})";
                    $results['totalToImport']++;
                    continue;
                }

                $participant = new Participant();
                $participant->setEmail($record['email']);
                $participant->setNom($record['nom']);
                $participant->setPrenom($record['prenom']);
                $participant->setPseudo($record['pseudo']);
                $participant->setTelephone($record['telephone'] ?? null);
                $participant->setAdministrateur((bool)$record['administrateur']);
                $participant->setActif((bool)$record['actif']);
                $participant->setSite($site);

                // Génère un mot de passe temporaire hash
                $hashedPassword = $this->passwordHasher->hashPassword($participant, 'changeme');
                $participant->setPassword($hashedPassword);

                $this->em->persist($participant);
                $results['imported']++;
                $results['totalToImport']++;
            } catch (\Throwable $e) {
                $results['errors'][] = "Ligne $i : " . $e->getMessage();
            }
        }

        $this->em->flush();

        return $results;
    }
}
