<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
class Admin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $admin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdmin(): ?string
    {
        return $this->admin;
    }

    public function setAdmin(string $admin): static
    {
        $this->admin = $admin;

        return $this;
    }
}
