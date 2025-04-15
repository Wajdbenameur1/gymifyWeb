<?php

namespace App\Entity;


use App\Repository\EventsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;


use App\Enum\EventType;
use App\Enum\Reward;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]

class Events
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]

    #[Assert\NotBlank]
    private ?string $nom = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotNull]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    private ?\DateTimeInterface $heure_debut = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    private ?\DateTimeInterface $heure_fin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_url = null;

    #[ORM\Column(type: 'string', enumType: EventType::class)]
    #[Assert\NotNull]
    private ?EventType $type = null;

    #[ORM\Column(type: 'string', enumType: Reward::class)]
    #[Assert\NotNull]
    private ?Reward $reward = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $lieu = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $longitude = null;

    #[ORM\ManyToOne(targetEntity: Salle::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Salle $salle = null;


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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heure_debut;
    }

    public function setHeureDebut(\DateTimeInterface $heure_debut): static
    {
        $this->heure_debut = $heure_debut;

        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heure_fin;
    }

    public function setHeureFin(\DateTimeInterface $heure_fin): static
    {
        $this->heure_fin = $heure_fin;


    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }


    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heure_debut;
    }

    public function setHeureDebut(\DateTimeInterface $heure_debut): self
    {
        $this->heure_debut = $heure_debut;
        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heure_fin;
    }

    public function setHeureFin(\DateTimeInterface $heure_fin): self
    {
        $this->heure_fin = $heure_fin;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }


    public function setImageUrl(?string $image_url): self
    {
        $this->image_url = $image_url;
        return $this;
    }

    public function getType(): ?EventType

    {
        return $this->type;
    }


    public function setType(?EventType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getReward(): ?Reward
    {
        return $this->reward;
    }


    public function setReward(Reward $reward): self
    {
        $this->reward = $reward;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }


    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }


    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }


    public function setSalle(?Salle $salle): self
    {
        $this->salle = $salle;
        return $this;
    }
}

