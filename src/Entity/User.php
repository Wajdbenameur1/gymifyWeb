<?php
namespace App\Entity;
use App\Enum\Role;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "user")]
#[UniqueEntity("email", message: "L'email {{ value }} est déjà utilisé.")]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "role", type: "string")]
#[ORM\DiscriminatorMap([ 
    "sportif" => Sportif::class, 
    "admin" => Admin::class, 
    "responsable_salle" => ResponsableSalle::class, 
    "entraineur" => Entraineur::class 
])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "Veuillez entrer une adresse email valide.")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateNaissance = null;

    // Relation OneToMany avec Post (Les posts d'un utilisateur)
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Post::class)]
    private Collection $posts;

   #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class)]
private Collection $comments;
#[ORM\OneToMany(mappedBy: 'entaineur', targetEntity: Cours::class)]
private Collection $cours;



#[ORM\Column(type: 'string', length: 100, nullable: true)]
private ?string $specialite = null;
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Reactions::class, cascade: ['persist', 'remove'])]
private Collection $reactions;


    public function __construct()
    {
        $this->role = Role::SPORTIF; 
        $this->entaineur = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    

    }

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

        public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

  
    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): static
    {
        $this->specialite = $specialite;
        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    

    /**
     * @return Collection<int, Cours>
     */
    public function getEntaineur(): Collection
    {
        return $this->entaineur;
    }

    public function addEntaineur(Cours $entaineur): static
    {
        if (!$this->entaineur->contains($entaineur)) {
            $this->entaineur[] = $entaineur;
            $entaineur->setEntaineurId($this);
        }
        return $this;
    }

    public function removeEntaineur(Cours $entaineur): static
    {
        if ($this->entaineur->removeElement($entaineur) && $entaineur->getEntaineur() === $this) {
            $entaineur->setEntaineur(null);
        }
        return $this;
    }

  

public function getRole(): Role
{
    return match (true) {
        $this instanceof \App\Entity\Admin => Role::ADMIN,
        $this instanceof \App\Entity\Sportif => Role::SPORTIF,
        $this instanceof \App\Entity\ResponsableSalle => Role::RESPONSABLE_SALLE,
        $this instanceof \App\Entity\Entraineur => Role::ENTRAINEUR,
        default => Role::SPORTIF,
    };
}

public function setRole(Role $role): self
{
    $this->role = $role;
    return $this;
}
    

    public function getRoles(): array
    {
        return match (true) {
            $this instanceof \App\Entity\Admin => ['ROLE_ADMIN'],
            $this instanceof \App\Entity\Sportif => ['ROLE_SPORTIF'],
            $this instanceof \App\Entity\ResponsableSalle => ['ROLE_RESPONSABLE_SALLE'],
            $this instanceof \App\Entity\Entraineur => ['ROLE_ENTRAINEUR'],
            default => ['ROLE_USER'],
        };
    }
    public function eraseCredentials(): void
    {
        // No sensitive data to erase
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}



