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

        return $this;
    }
}
