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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;  // Ajouter cet import
use Symfony\Component\Validator\Constraints as Assert;

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
/**
 * @UniqueEntity("email", message="L'email {{ value }} est déjà utilisé.")  // Ajoutez la contrainte UniqueEntity ici
 */
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

    #[ORM\Column(length: 100, unique: true)]  // L'email est unique dans la base de données
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "Veuillez entrer une adresse email valide.")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: 'string', enumType: Role::class)]
    private ?Role $role = null;

  

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
    #[ORM\Column(type: 'string', length: 100, nullable: true)] // Add nullable if specialite is optional
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
    public function getSpecialiteIfEntraineur(): ?string
    {
        // Vérifie si l'utilisateur est un Entraineur et retourne sa spécialité
        if ($this instanceof Entraineur) {
            return $this->getSpecialite();
        }

        return null;  // Retourne null si ce n'est pas un Entraineur
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
    $roles = [];
    if ($this->role) {
        $roles[] = 'ROLE_' . $this->role->value;
    }
    return array_unique($roles);
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
