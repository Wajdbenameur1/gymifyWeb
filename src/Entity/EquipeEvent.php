<?php

namespace App\Entity;

use App\Repository\EquipeEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipeEventRepository::class)]
class EquipeEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'equipe')]
    private ?equipe $equipe = null;

    #[ORM\ManyToOne(inversedBy: 'event')]
    private ?events $event = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEquipe(): ?equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?equipe $equipe): static
    {
        $this->equipe = $equipe;

        return $this;
    }

    public function getEvent(): ?events
    {
        return $this->event;
    }

    public function setEvent(?events $event): static
    {
        $this->event = $event;

        return $this;
    }
}
