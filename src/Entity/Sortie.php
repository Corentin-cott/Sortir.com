<?php

namespace App\Entity;

use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?\DateTime $dateHeureDebut = null;

    #[ORM\Column]
    private ?int $duree = null;

    #[ORM\Column]
    private ?\DateTime $dateLimiteInscription = null;

    #[ORM\Column]
    private ?int $nbInscriptionMax = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descriptionInfos = null;

    #[ORM\ManyToOne(inversedBy: 'sortiesOrg')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $organisateur = null;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'sorties')]
    private Collection $participants;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $siteOrg = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lieu $lieu = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTime
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTime $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }


    public function getDateLimiteInscription(): ?\DateTime
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTime $dateLimiteInscription): self
    {
        $this->dateLimiteInscription = $dateLimiteInscription;
        return $this;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function setNbInscriptionMax(int $nbInscriptionMax): self
    {
        $this->nbInscriptionMax = $nbInscriptionMax;
        return $this;
    }

    public function getDescriptionInfos(): ?string
    {
        return $this->descriptionInfos;
    }

    public function setDescriptionInfos(?string $descriptionInfos): static
    {
        $this->descriptionInfos = $descriptionInfos;

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getSiteOrg(): ?Site
    {
        return $this->siteOrg;
    }

    public function setSiteOrg(?Site $siteOrg): static
    {
        $this->siteOrg = $siteOrg;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function MiseAJourEtat(EtatRepository $etatRepository): void
    {
        $now = new \DateTime();

        // Raccourcis pour les états disponibles
        $etatCree = $etatRepository->findOneBy(['libelle' => 'Créée']);
        $etatOuverte = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
        $etatCloturee = $etatRepository->findOneBy(['libelle' => 'Clôturée']);
        $etatEnCours = $etatRepository->findOneBy(['libelle' => 'Activitée en cours']);
        $etatPasse = $etatRepository->findOneBy(['libelle' => 'Passée']);
        $etatAnnule = $etatRepository->findOneBy(['libelle' => 'Annulée']);

        // Si la sortie est annulée → on ne touche plus à son état
        if ($this->etat === $etatAnnule) {
            return;
        }

        // Si la date limite est passée et qu'elle est encore ouverte → Clôturée
        if ($this->getDateLimiteInscription() < $now && $this->etat === $etatOuverte) {
            $this->etat = $etatCloturee;
            return;
        }

        // Si la sortie commence maintenant → En cours
        if ($this->getDateHeureDebut() <= $now && $this->etat !== $etatPasse) {
            $this->etat = $etatEnCours;
            return;
        }

        // Si la sortie est terminée (date + durée)
        $fin = (clone $this->getDateHeureDebut())->modify('+' . $this->getDuree() . ' minutes');
        if ($fin < $now) {
            $this->etat = $etatPasse;
        }
    }



    /**
     * @throws \Exception
     */
    public function sinscrire(Participant $participant): void
    {
        $dateNow = new \DateTime();
        //Cloture des inscriptions
        if($dateNow > $this->getDateLimiteInscription())
        {
            throw new \Exception("La date limite d'inscription est passée");
        }
        //nbMax
        $nbInscrit = count($this->getParticipants());
        if($nbInscrit == $this->getNbInscriptionMax()){
            throw new \Exception("Le nombre maximum d'inscrits est deja atteint");
        }
        $this->addParticipant($participant);
    }

    /**
     * @throws \Exception
     */
    public function desinscrire(Participant $participant): void
    {
        if (!$this->estInscrit($participant)) {
            throw new \Exception("Vous n'êtes pas inscrit à cette sortie.");
        }
        $this->removeParticipant($participant);
    }
    public function estInscrit(Participant $user): bool
    {
       foreach ($this->getParticipants() as $participant) {
           if($participant->getId() === $user->getId()) {
               return true;
           }
       }
       return false;
    }

}
