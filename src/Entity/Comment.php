<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use App\Entity\Post;  // Importation de l'entité Post
use App\Entity\User;  // Importation de l'entité User
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column(name: 'createdAt', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;


     // Relation ManyToOne avec Post
     #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
     #[ORM\JoinColumn(name: 'postId', referencedColumnName: 'id')]
     private ?Post $post = null;
 
     // Relation ManyToOne avec User
     #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
     #[ORM\JoinColumn(name: 'id_User', referencedColumnName: 'id')]
     private ?User $user = null;
 
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
  // Getter et Setter pour la relation ManyToOne avec Post
  public function getPost(): ?Post
  {
      return $this->post;
  }

  public function setPost(?Post $post): static
  {
      $this->post = $post;
      return $this;
  }

  // Getter et Setter pour la relation ManyToOne avec User
  public function getUser(): ?User
  {
      return $this->user;
  }

  public function setUser(?User $user): static
  {
      $this->user = $user;
      return $this;
  }
}
