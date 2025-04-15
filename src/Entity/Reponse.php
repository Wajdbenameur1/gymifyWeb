<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

<<<<<<< HEAD
    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateReponse = null;

    public function getId(): ?int
    {
        return $this->id_Reponse;
=======
    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;
    #[ORM\Column(name: 'dateReponse', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateReponse = null;


    #[ORM\ManyToOne(targetEntity: Reclamation::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reclamation $reclamation = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

<<<<<<< HEAD
    public function setMessage(string $message): static
    {
        $this->message = $message;

=======
    public function setMessage(string $message): self
    {
        $this->message = $message;
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
        return $this;
    }

    public function getDateReponse(): ?\DateTimeInterface
    {
        return $this->dateReponse;
    }

<<<<<<< HEAD
    public function setDateReponse(\DateTimeInterface $dateReponse): static
    {
        $this->dateReponse = $dateReponse;

        return $this;
    }
}
=======
    public function setDateReponse(\DateTimeInterface $dateReponse): self
    {
        $this->dateReponse = $dateReponse;
        return $this;
    }

    public function getReclamation(): ?Reclamation
    {
        return $this->reclamation;
    }

    public function setReclamation(?Reclamation $reclamation): self
    {
        $this->reclamation = $reclamation;
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
