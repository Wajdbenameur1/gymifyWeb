<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Niveau;


#[ORM\Entity(repositoryClass: EquipeRepository::class)]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $image_url = null;

    #[ORM\Column(type: 'string',enumType: Niveau::class,/*columnDefinition: "ENUM('PERSONAL_TRAINING', 'GROUP_ACTIVITY', 'FITNESS_CONSULTATION')"*/)]
    private ?Niveau $niveau = null;

    #[ORM\Column]
    private ?int $nombre_membres = null;

    /**
     * @var Collection<int, EquipeEvent>
     */
    #[ORM\OneToMany(targetEntity: EquipeEvent::class, mappedBy: 'equipe')]
    private Collection $equipe;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'equipe')]
    private Collection $equipes;

    

    public function __construct()
    {
        $this->id_equipe = new ArrayCollection();
        $this->equipe = new ArrayCollection();
        $this->equipes = new ArrayCollection();
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

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(string $image_url): static
    {
        $this->image_url = $image_url;

        return $this;
    }

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(Niveau $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getNombreMembres(): ?int
    {
        return $this->nombre_membres;
    }

    public function setNombreMembres(int $nombre_membres): static
    {
        $this->nombre_membres = $nombre_membres;

        return $this;
    }

    /**
     * @return Collection<int, EquipeEvent>
     */
    public function getEquipe(): Collection
    {
        return $this->equipe;
    }

    public function addEquipe(EquipeEvent $equipe): static
    {
        if (!$this->equipe->contains($equipe)) {
            $this->equipe->add($equipe);
            $equipe->setEquipe($this);
        }

        return $this;
    }

    public function removeEquipe(EquipeEvent $equipe): static
    {
        if ($this->equipe->removeElement($equipe)) {
            // set the owning side to null (unless already changed)
            if ($equipe->getEquipe() === $this) {
                $equipe->setEquipe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getEquipes(): Collection
    {
        return $this->equipes;
    }

  }