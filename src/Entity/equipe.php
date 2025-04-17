<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'integer')]
    private ?int $nombreMembres = 0;

    #[ORM\OneToMany(mappedBy: 'equipe', targetEntity: User::class)]
    private Collection $membres;

    #[ORM\OneToMany(mappedBy: 'equipe', targetEntity: EquipeEvent::class)]
    private Collection $equipeEvents;

    public function __construct()
    {
        $this->membres = new ArrayCollection();
        $this->equipeEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getNombreMembres(): ?int
    {
        return $this->nombreMembres;
    }

    public function setNombreMembres(int $nombreMembres): self
    {
        $this->nombreMembres = $nombreMembres;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembres(): Collection
    {
        return $this->membres;
    }

    public function addMembre(User $membre): self
    {
        if (!$this->membres->contains($membre)) {
            $this->membres[] = $membre;
            $membre->setEquipe($this);
        }

        return $this;
    }

    public function removeMembre(User $membre): self
    {
        if ($this->membres->removeElement($membre)) {
            if ($membre->getEquipe() === $this) {
                $membre->setEquipe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EquipeEvent>
     */
    public function getEquipeEvents(): Collection
    {
        return $this->equipeEvents;
    }

    public function addEquipeEvent(EquipeEvent $equipeEvent): self
    {
        if (!$this->equipeEvents->contains($equipeEvent)) {
            $this->equipeEvents[] = $equipeEvent;
            $equipeEvent->setEquipe($this);
        }

        return $this;
    }

    public function removeEquipeEvent(EquipeEvent $equipeEvent): self
    {
        if ($this->equipeEvents->removeElement($equipeEvent)) {
            if ($equipeEvent->getEquipe() === $this) {
                $equipeEvent->setEquipe(null);
            }
        }

        return $this;
    }
}