<?php

namespace App\Entity;

use App\Enum\ActivityType;
use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activité
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 300)]
    private ?string $description = null;

    #[ORM\Column(length: 200)]
    private ?string $url = null;

    #[ORM\Column(type: 'string',enumType: ActivityType::class,columnDefinition: "ENUM('PERSONAL_TRAINING', 'GROUP_ACTIVITY', 'FITNESS_CONSULTATION')")]
    private ?ActivityType $type = null;

    /**
     * @var Collection<int, abonnement>
     */
    #[ORM\OneToMany(targetEntity: Abonnement::class, mappedBy: 'activite')]
    private Collection $activite;

    /**
     * @var Collection<int, cours>
     */
    #[ORM\OneToMany(targetEntity: Cours::class, mappedBy: 'activité')]
    private Collection $activité;

    public function __construct()
    {
        $this->activite = new ArrayCollection();
        $this->activité = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getType(): ?ActivityType
    {
        return $this->type;
    }

    public function setType(ActivityType $type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Collection<int, abonnement>
     */
    public function getActivite(): Collection
    {
        return $this->activite;
    }

    public function addActivite(Abonnement $activite): static
    {
        if (!$this->activite->contains($activite)) {
            $this->activite->add($activite);
            $activite->setActivite($this);
        }

        return $this;
    }

    public function removeActivite(Abonnement $activite): static
    {
        if ($this->activite->removeElement($activite)) {
            // set the owning side to null (unless already changed)
            if ($Activite->getActivite() === $this) {
                $Activite->setActivite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, cours>
     */
    public function getActivité(): Collection
    {
        return $this->activité;
    }

    public function addActivité(Cours $activité): static
    {
        if (!$this->activité->contains($activité)) {
            $this->activité->add($activité);
            $activité->setActivité($this);
        }

        return $this;
    }

    public function removeActivité(Cours $activité): static
    {
        if ($this->activité->removeElement($activité)) {
            // set the owning side to null (unless already changed)
            if ($activité->getActivité() === $this) {
                $activité->setActivité(null);
            }
        }

        return $this;
    }
}
