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

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "user")]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "type", type: "string")]
#[ORM\DiscriminatorMap([
    "sportif" => Sportif::class,
    "admin" => Admin::class,
    "responsable_salle" => ResponsableSalle::class,
    "entraineur"=>Entraineur::class
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

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: 'string', enumType: Role::class)]
    private ?Role $role = null;

   #[ORM\Column(length: 100, nullable: true)]
private ?string $specialite = null;
    

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateNaissance = null;

    /**
     * @var Collection<int, cours>
     */
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

   
    public function getRole(): ?string
{
    return $this->role ? $this->role->value : null;
}
    
    public function setRole(Role $role): static
{
    $this->role = $role;
    return $this;
} 

public function getRoles(): array
{

    if ($this->role) {
        $roles[] = 'ROLE_' . strtoupper($this->role->value);
    }
    return array_unique($roles);  // Retourne les rôles sans doublons
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
    
    

    public function setImageUrl(string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

   

    public function setDateNaissance(\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    /**
     * @return Collection<int, cours>
     */
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
            // set the owning side to null (unless already changed)
            if ($entaineur->getEntaineur() === $this) {
                $entaineur->setEntaineur(null);
            }
        }

        return $this;
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

    
}
