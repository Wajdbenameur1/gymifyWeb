<?php
namespace App\Entity;

use App\Enum\Role;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;  
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "user")]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "role", type: "string")]  // Discriminateur pour distinguer les rôles
#[ORM\DiscriminatorMap([
    'sportif' => Sportif::class,
    'entraineur' => Entraineur::class,
    'admin' => Admin::class,
    'responsable_salle' => ResponsableSalle::class,
])]
#[UniqueEntity("email", message: "L'email {{ value }} est déjà utilisé.")]
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

    #[ORM\OneToMany(targetEntity: Cours::class, mappedBy: 'entaineur')]
    private Collection $entaineur;

    #[ORM\ManyToOne(inversedBy: 'equipes')]
    private ?Equipe $equipe = null;

    public function __construct()
    {
        $this->entaineur = new ArrayCollection();
    }

    public function eraseCredentials(): void
    {
        // Nettoyer les données sensibles ici si nécessaire (ex: plainPassword)
    }

    public function getUserIdentifier(): string
    {
        return $this->email;  // Utiliser l'email comme identifiant
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

    #[ORM\Column(type: 'string', length: 100, nullable: true)] 
    private ?string $specialite;

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(?string $specialite): static
    {
        $this->specialite = $specialite;
        return $this;
    }

    // La méthode 'getRole()' et 'setRole()' ne sont plus nécessaires ici, puisque le rôle est géré par la colonne de discrimination.
    public function getRoles(): array
    {
        // Utiliser le nom de la classe actuelle pour déterminer le rôle
        $roleClass = get_class($this);
        $role = strtolower(substr($roleClass, strrpos($roleClass, '\\') + 1));  // Ex: "Sportif", "Admin", etc.
        
        return ['ROLE_' . strtoupper($role)];  // Format correct du rôle
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
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

    public function setDateNaissance(\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    public function getEntaineur(): Collection
    {
        return $this->entaineur;
    }

    public function addEntaineur(cours $entaineur): static
    {
        if (!$this->entaineur->contains($entaineur)) {
            $this->entaineur->add($entaineur);
            $entaineur->setEntaineurId($this);
        }
        return $this;
    }

    public function removeEntaineur(cours $entaineur): static
    {
        if ($this->entaineur->removeElement($entaineur)) {
            if ($entaineur->getEntaineur() === $this) {
                $entaineur->setEntaineur(null);
            }
        }
        return $this;
    }

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): static
    {
        $this->equipe = $equipe;
        return $this;
    }
}
