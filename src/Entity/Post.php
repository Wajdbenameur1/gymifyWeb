<?php

namespace App\Entity;

use App\Repository\PostRepository;
use App\Entity\User;  // Ajout de l'entité User
use App\Entity\Comment;  // Ajout de l'entité Comment
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_url = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    // Relation ManyToOne avec User
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(name: 'id_User', referencedColumnName: 'id')]
    private ?User $user = null;  // Cette propriété représente l'utilisateur auquel le post appartient.

    // Relation OneToMany avec Comment
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class)]
    private Collection $comments;

    /**
     * @var Collection<int, Reactions>
     */
    #[ORM\OneToMany(targetEntity: Reactions::class, mappedBy: 'post')]
    private Collection $reactions;

  

    
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
       
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
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

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(?string $image_url): static
    {
        $this->image_url = $image_url;
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

    // Getter et Setter pour la relation OneToMany avec Comment
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);  // Associe ce post au commentaire
        }
        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // Désassocie ce post du commentaire
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Reactions>
     */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function addReaction(Reactions $reaction): static
    {
        if (!$this->reactions->contains($reaction)) {
            $this->reactions->add($reaction);
            $reaction->setPost($this);
        }

        return $this;
    }

    public function removeReaction(Reactions $reaction): static
    {
        if ($this->reactions->removeElement($reaction)) {
            // set the owning side to null (unless already changed)
            if ($reaction->getPost() === $this) {
                $reaction->setPost(null);
            }
        }

        return $this;
    }

   

}