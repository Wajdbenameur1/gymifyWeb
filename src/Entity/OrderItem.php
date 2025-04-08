<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'commande_produit')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;

    #[ORM\Column(name: 'quantite')]
    private ?int $quantity = null;

    #[ORM\Column(name: 'prix_unitaire')]
    private ?float $unitPrice = null;

    #[ORM\Column(name: 'sous_total')]
    private ?float $subtotal = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(name: 'commande_id', referencedColumnName: 'id', nullable: false)]
    private ?Order $order = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'produit_id', referencedColumnName: 'id_p', nullable: false)]
    private ?Produit $produit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        $this->calculateSubtotal();
        return $this;
    }

    public function getUnitPrice(): ?float
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(float $unitPrice): static
    {
        $this->unitPrice = $unitPrice;
        $this->calculateSubtotal();
        return $this;
    }

    public function getSubtotal(): ?float
    {
        return $this->subtotal;
    }

    public function setSubtotal(float $subtotal): static
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;
        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;
        if ($produit) {
            $this->setUnitPrice($produit->getPrix());
        }
        return $this;
    }

    private function calculateSubtotal(): void
    {
        if ($this->quantity !== null && $this->unitPrice !== null) {
            $this->subtotal = $this->quantity * $this->unitPrice;
        }
    }
} 