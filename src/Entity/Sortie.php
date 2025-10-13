<?php

namespace App\Entity;

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
    private ?\DateTimeImmutable $dateHeureDebut = null;

    #[ORM\Column]
    private ?int $duree = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateLimiteInscription = null;

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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $annulation_motif = null;

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

    public function getDateHeureDebut(): ?\DateTimeImmutable
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeImmutable $dateHeureDebut): static
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


    public function getDateLimiteInscription(): ?\DateTimeImmutable
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeImmutable $dateLimiteInscription): self
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

    /**
     * @throws \Exception
     */
    public function sinscrire(Participant $participant): void
    {
        $dateNow = new \DateTimeImmutable()->format('Y-m-d H:i:s');
        $dateLimite = $this->getDateLimiteInscription()->format('Y-m-d H:i:s');

        //User is organisateur
        if($participant->getId() == $this->getOrganisateur()->getId()){
            throw new \Exception("Vous ne pouvez pas vous inscrire en tant qu'organisateur");
        }

        if(!$participant->isActif()){
            throw new \Exception("Votre compte a été désactiver vous ne pouvez pas faire cette action");
        }

        if($this->getEtat()->getLibelle() != "Ouverte"){
            throw new \Exception("Le statut de la sortie ne permet pas d'inscription.");
        }

        //Cloture des inscriptions
        if($dateNow > $dateLimite)
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

    public function getAnnulationMotif(): ?string
    {
        return $this->annulation_motif;
    }

    public function setAnnulationMotif(?string $annulation_motif): static
    {
        $this->annulation_motif = $annulation_motif;

        return $this;
    }

}
