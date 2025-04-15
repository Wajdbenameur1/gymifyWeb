<?php

namespace App\Entity;

use App\Repository\AbonnementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
class Abonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_Abonnement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_Debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_Fin = null;

    #[ORM\Column(length: 255)]
    private ?string $type_Abonnement = null;

    #[ORM\Column]
    private ?float $tarif = null;

    #[ORM\ManyToOne(inversedBy: 'id_activite')]
    private ?Activité $activite = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_Debut;
    }

    public function setDateDebut(\DateTimeInterface $date_Debut): static
    {
        $this->date_Debut = $date_Debut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_Fin;
    }

    public function setDateFin(\DateTimeInterface $date_Fin): static
    {
        $this->date_Fin = $date_Fin;

        return $this;
    }

    public function getTypeAbonnement(): ?string
    {
        return $this->type_Abonnement;
    }

    public function setTypeAbonnement(string $type_Abonnement): static
    {
        $this->type_Abonnement = $type_Abonnement;

        return $this;
    }

    public function getTarif(): ?float
    {
        return $this->tarif;
    }

    public function setTarif(float $tarif): static
    {
        $this->tarif = $tarif;

        return $this;
    }

    public function getActivite(): ?Activité
    {
        return $this->activite;
    }

    public function setActivite(?Activité $activite): static
    {
        $this->activite = $activite;

        return $this;
    }
}
