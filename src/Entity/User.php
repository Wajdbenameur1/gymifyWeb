<?php
namespace App\Entity;

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

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $specialite = null;

    #[ORM\OneToMany(targetEntity: Cours::class, mappedBy: 'entaineur')]
    private Collection $entaineur;

    #[ORM\Column(length: 20)]
    private string $role;

    #[ORM\ManyToOne(inversedBy: 'equipes')]
    private ?Equipe $equipe = null;

    public function __construct()
    {
        $this->entaineur = new ArrayCollection();
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

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;
        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(?string $specialite): static
    {
        $this->specialite = $specialite;
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

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): static
    {
        $this->equipe = $equipe;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getRoles(): array
    {
        // Prefix the role with ROLE_ and convert to uppercase to match security.yaml
        $role = $this->role ? 'ROLE_' . strtoupper($this->role) : 'ROLE_USER';
        return [$role];
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