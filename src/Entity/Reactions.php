<?php

namespace App\Entity;
use App\Entity\User;
use App\Entity\Post;

use App\Repository\ReactionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReactionsRepository::class)]
class Reactions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reactions')]
    #[ORM\JoinColumn(name: "id_User", referencedColumnName: "id")]  // Définir le nom de la colonne

private ?User $user = null;

#[ORM\ManyToOne(inversedBy: 'reactions')]
#[ORM\JoinColumn(name: "postId", referencedColumnName: "id")]  // Définir le nom de la colonne

private ?Post $post = null;
#[ORM\Column(type: "string", length: 10)]
    private ?string $type = null;  // Ajout du champ 'type'

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;
        return $this;
    }

    // Getter et Setter pour 'type'
    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }
}