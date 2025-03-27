<?php

namespace App\Entity;

use App\Repository\PlanningRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningRepository::class)]
class Planning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(length: 300)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;
    #[ORM\ManyToOne(inversedBy: 'entaineur')]
    private ?User $entaineur = null;

    /**
     * @var Collection<int, cours>
     */
    #[ORM\OneToMany(targetEntity: cours::class, mappedBy: 'planning')]
    private Collection $planning;

    public function __construct()
    {
        $this->planning = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
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

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * @return Collection<int, cours>
     */
    public function getPlanning(): Collection
    {
        return $this->planning;
    }

    public function addPlanning(Cours $planning): static
    {
        if (!$this->planning_id->contains($planning)) {
            $this->planning->add($planning);
            $planning->setPlanning($this);
        }

        return $this;
    }

    public function removePlanning(Cours $planning): static
    {
        if ($this->planning_id->removeElement($planning)) {
            // set the owning side to null (unless already changed)
            if ($planning->getPlanning() === $this) {
                $planning->setPlanning(null);
            }
        }

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
}
