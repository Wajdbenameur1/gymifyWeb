<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use App\Enum\ObjectifCours;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: 'string',enumType: ObjectifCours::class,columnDefinition: "ENUM('PERTE_PROIDS','PRISE_DE_MASSE','ENDURANCE','RELAXATION')")]
    private ?ObjectifCours $objectif = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heurDebut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heurFin = null;

    #[ORM\ManyToOne(inversedBy: 'activité')]
    private ?Activité $activité = null;

    #[ORM\ManyToOne(inversedBy: 'planning')]
    private ?Planning $planning = null;

    #[ORM\ManyToOne(inversedBy: 'entaineur')]
    private ?User $entaineur = null;

    #[ORM\ManyToOne(inversedBy: 'salle')]
    private ?Salle $salle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getObjectif(): ?ObjectifCours
    {
        return $this->objectif;
    }

    public function setObjectif(ObjectifCours $objectif): static
    {
        $this->objectif = $objectif;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getHeurDebut(): ?\DateTimeInterface
    {
        return $this->heurDebut;
    }

    public function setHeurDebut(\DateTimeInterface $heurDebut): static
    {
        $this->heurDebut = $heurDebut;

        return $this;
    }

    public function getHeurFin(): ?\DateTimeInterface
    {
        return $this->heurFin;
    }

    public function setHeurFin(\DateTimeInterface $heurFin): static
    {
        $this->heurFin = $heurFin;

        return $this;
    }

    public function getActivité(): ?Activité
    {
        return $this->activité;
    }

    public function setActivité(?Activité $activité): static
    {
        $this->activité = $activité;

        return $this;
    }

    public function getPlanning(): ?Planning
    {
        return $this->planning;
    }

    public function setPlanning(?Planning $planning): static
    {
        $this->planning = $planning;

        return $this;
    }

    public function getEntaineur(): ?User
    {
        return $this->entaineur;
    }

    public function setEntaineur(?User $entaineur): static
    {
        $this->entaineur = $entaineur;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): static
    {
        $this->salle = $salle;

        return $this;
    }
}
