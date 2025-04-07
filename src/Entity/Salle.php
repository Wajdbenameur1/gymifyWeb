<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $nom = null;

    #[ORM\Column(length: 200)]
    private ?string $adresse = null;

    #[ORM\Column(length: 500)]
    private ?string $details = null;

    #[ORM\Column(length: 50)]
    private ?string $num_tel = null;

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\Column(length: 500)]
    private ?string $url_photo = null;

    /**
     * @var Collection<int, cours>
     */
    #[ORM\OneToMany(targetEntity: cours::class, mappedBy: 'salle')]
    private Collection $salle;

    /**
     * @var Collection<int, Events>
     */
    #[ORM\OneToMany(targetEntity: Events::class, mappedBy: 'salle')]
    private Collection $salles;

    

    public function __construct()
    {
        $this->salle = new ArrayCollection();
        $this->id_salle = new ArrayCollection();
        $this->salles = new ArrayCollection();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getNumTel(): ?string
    {
        return $this->num_tel;
    }

    public function setNumTel(string $num_tel): static
    {
        $this->num_tel = $num_tel;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUrlPhoto(): ?string
    {
        return $this->url_photo;
    }

    public function setUrlPhoto(string $url_photo): static
    {
        $this->url_photo = $url_photo;

        return $this;
    }

    /**
     * @return Collection<int, Cours>
     */
    public function getSalle(): Collection
    {
        return $this->salle;
    }

    public function addSalle(Cours $salle): static
    {
        if (!$this->salle->contains($salle)) {
            $this->salle->add($salle);
            $salle->setSalleId($this);
        }

        return $this;
    }

    public function removeSalle(Cours $salle): static
    {
        if ($this->salle->removeElement($salle)) {
            // set the owning side to null (unless already changed)
            if ($salle->getSalle() === $this) {
                $salle->setSalle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Events>
     */
    public function getSalles(): Collection
    {
        return $this->salles;
    }

    
}
