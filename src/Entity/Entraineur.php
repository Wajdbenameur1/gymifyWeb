<?php

namespace App\Entity;

use App\Repository\EntraineurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntraineurRepository::class)]
class Entraineur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Entraineur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntraineur(): ?string
    {
        return $this->Entraineur;
    }

    public function setEntraineur(string $Entraineur): static
    {
        $this->Entraineur = $Entraineur;

        return $this;
    }
}
