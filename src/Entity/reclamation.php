<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $sujet = null;

<<<<<<< HEAD
    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;
=======
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'En attente';

    #[ORM\Column(name: 'dateCreation', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;
    

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false)]
    private ?User $user = null;
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

<<<<<<< HEAD
    public function setSujet(string $sujet): static
    {
        $this->sujet = $sujet;

=======
    public function setSujet(string $sujet): self
    {
        $this->sujet = $sujet;
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

<<<<<<< HEAD
    public function setDescription(string $description): static
    {
        $this->description = $description;

=======
    public function setDescription(string $description): self
    {
        $this->description = $description;
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

<<<<<<< HEAD
    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }
}
=======
    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }
    public function __construct()
{
    $this->dateCreation = new \DateTime();
}

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
