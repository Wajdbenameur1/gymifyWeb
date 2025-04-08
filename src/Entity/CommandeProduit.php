<?php

namespace App\Entity;

use App\Repository\CommandeProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeProduitRepository::class)]
#[ORM\Table(name: 'commande_produit')]
class CommandeProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandeProduits')]
    #[ORM\JoinColumn(name: 'commande_id', referencedColumnName: 'id_c', nullable: false)]
    private ?Commande $commande = null;

    #[ORM\ManyToOne(inversedBy: 'commandeProduits')]
    #[ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'id_p', nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(name: 'quantite')]
    #[Assert\NotNull(message: 'Quantity is required')]
    #[Assert\Positive(message: 'Quantity must be positive')]
    private ?int $quantite = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;
        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }
} 