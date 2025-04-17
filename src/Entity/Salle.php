<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: "Le nom de la salle est requis.")]
    #[Assert\Length(
        max: 200,
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: "L’adresse est requise.")]
    #[Assert\Length(
        max: 200,
        maxMessage: "L’adresse ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $adresse = null;

    #[ORM\Column(length: 500)]
    #[Assert\NotBlank(message: "Les détails sont requis.")]
    #[Assert\Length(
        max: 500,
        maxMessage: "Les détails ne peuvent pas dépasser {{ limit }} caractères."
    )]
    private ?string $details = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le numéro de téléphone est requis.")]
    #[Assert\Regex(
        pattern: "/^\+216\s\d{2}\s\d{3}\s\d{3}$/",
        message: "Le format doit être: +216 XX XXX XXX"
    )]
    private ?string $num_tel = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "L’email est requis.")]
    #[Assert\Email(message: "L’email n’est pas valide.")]
    #[Assert\Length(
        max: 100,
        maxMessage: "L’email ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $email = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Length(
        max: 500,
        maxMessage: "Le chemin de l’image ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $url_photo = null;

    /**
     * @var Collection<int, Cours>
     */
    #[ORM\OneToMany(targetEntity: Cours::class, mappedBy: 'salle')]
    private Collection $cours;

    #[ORM\OneToMany(targetEntity: Activité::class, mappedBy: 'salle')]
    private Collection $activites;

    /**
     * @var Collection<int, Events>
     */
    #[ORM\OneToMany(targetEntity: Events::class, mappedBy: 'salle')]
    private Collection $events;

    #[ORM\OneToOne(targetEntity: ResponsableSalle::class, inversedBy: 'salle')]
    #[ORM\JoinColumn(name: 'responsable_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: "Le responsable est requis.")]
    private ?ResponsableSalle $responsable = null;

    public function __construct()
    {
        $this->cours = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->activites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function getNumTel(): ?string
    {
        return $this->num_tel;
    }

    public function setNumTel(string $num_tel): self
    {
        $this->num_tel = $num_tel;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUrlPhoto(): ?string
    {
        return $this->url_photo;
    }

    public function setUrlPhoto(?string $url_photo): self
    {
        $this->url_photo = $url_photo;
        return $this;
    }

    public function getResponsable(): ?ResponsableSalle
    {
        return $this->responsable;
    }

    public function setResponsable(?ResponsableSalle $responsable): self
    {
        $this->responsable = $responsable;
        return $this;
    }

    /**
     * @return Collection<int, Cours>
     */
    public function getCours(): Collection
    {
        return $this->cours;
    }

    public function addCour(Cours $cour): self
    {
        if (!$this->cours->contains($cour)) {
            $this->cours->add($cour);
            $cour->setSalle($this);
        }
        return $this;
    }

    public function removeCour(Cours $cour): self
    {
        if ($this->cours->removeElement($cour)) {
            if ($cour->getSalle() === $this) {
                $cour->setSalle(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Events>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Events $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setSalle($this);
        }
        return $this;
    }

    public function removeEvent(Events $event): self
    {
        if ($this->events->removeElement($event)) {
            if ($event->getSalle() === $this) {
                $event->setSalle(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }

    public function getActivites(): Collection
    {
        return $this->activites;
    }

    public function addActivite(Activité $activite): self
    {
        if (!$this->activites->contains($activite)) {
            $this->activites->add($activite);
            $activite->setSalle($this);
        }
        return $this;
    }

    public function removeActivite(Activité $activite): self
    {
        if ($this->activites->removeElement($activite)) {
            $activite->setSalle(null);
        }
        return $this;
    }

    public function getAbonnements(): array
    {
        $abonnements = [];
        foreach ($this->activites as $activite) {
            foreach ($activite->getAbonnements() as $abonnement) {
                $abonnements[] = $abonnement;
            }
        }
        return $abonnements;
    }
}