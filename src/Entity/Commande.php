<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'commande')]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_c')]
    private ?int $id = null;

    #[ORM\Column(name: 'total_c', type: 'float')]
    #[Assert\NotNull(message: 'Total is required')]
    #[Assert\PositiveOrZero(message: 'Total must be positive or zero')]
    private ?float $total = null;

    #[ORM\Column(name: 'statut_c', length: 50)]
    #[Assert\NotBlank(message: 'Status is required')]
    private ?string $statut = null;

    #[ORM\Column(name: 'dateC', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateC = null;

    #[ORM\Column(name: 'user_id')]
    private ?int $userId = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: CommandeProduit::class, orphanRemoval: true)]
    private Collection $commandeProduits;

   /* #[ORM\OneToOne(mappedBy: 'commande', targetEntity: Payment::class, cascade: ['persist', 'remove'])]
    private ?Payment $payment = null;*/

    public function __construct()
    {
        $this->commandeProduits = new ArrayCollection();
        $this->dateC = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateC(): ?\DateTimeInterface
    {
        return $this->dateC;
    }

    public function setDateC(\DateTimeInterface $dateC): static
    {
        $this->dateC = $dateC;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return Collection<int, CommandeProduit>
     */
    public function getCommandeProduits(): Collection
    {
        return $this->commandeProduits;
    }

    public function addCommandeProduit(CommandeProduit $commandeProduit): static
    {
        if (!$this->commandeProduits->contains($commandeProduit)) {
            $this->commandeProduits->add($commandeProduit);
            $commandeProduit->setCommande($this);
        }
        return $this;
    }

    public function removeCommandeProduit(CommandeProduit $commandeProduit): static
    {
        if ($this->commandeProduits->removeElement($commandeProduit)) {
            if ($commandeProduit->getCommande() === $this) {
                $commandeProduit->setCommande(null);
            }
        }
        return $this;
    }
/*
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): static
    {
        if ($payment === null && $this->payment !== null) {
            $this->payment->setCommande(null);
        }

        if ($payment !== null && $payment->getCommande() !== $this) {
            $payment->setCommande($this);
        }

        $this->payment = $payment;
        return $this;
    }*/
} 