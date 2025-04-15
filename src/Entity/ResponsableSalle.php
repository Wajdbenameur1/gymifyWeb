<?php

namespace App\Entity;

use App\Repository\ResponsableSalleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResponsableSalleRepository::class)]
class ResponsableSalle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ResponsableSalle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResponsableSalle(): ?string
    {
        return $this->ResponsableSalle;
    }

    public function setResponsableSalle(string $ResponsableSalle): static
    {
        $this->ResponsableSalle = $ResponsableSalle;

    /**
     * Associe une salle au responsable de salle.
     *
     * @param Salle|null $salle
     * @return self
     */
    public function setSalle(?Salle $salle): static
    {
        // Si la salle est dissociée, nous mettons à null le responsable dans la salle
        if ($salle === null && $this->salle !== null) {
            $this->salle->setResponsable(null);
        }

        // Si la salle est assignée, nous nous assurons qu'elle est bien associée au responsable
        if ($salle !== null && $salle->getResponsable() !== $this) {
             }

        $this->salle = $salle;
        return $this;
    }
}
