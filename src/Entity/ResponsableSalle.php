<?php

namespace App\Entity;

<<<<<<< HEAD
=======
use App\Enum\Role;
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
use App\Repository\ResponsableSalleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResponsableSalleRepository::class)]
<<<<<<< HEAD
class ResponsableSalle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ResponsableSalle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResponsableSalle(): ?string
    {
        return $this->ResponsableSalle;
    }

    public function setResponsableSalle(string $ResponsableSalle): static
    {
        $this->ResponsableSalle = $ResponsableSalle;

        return $this;
    }
}
=======
class ResponsableSalle extends User
{
    #[ORM\OneToOne(mappedBy: 'responsable', targetEntity: Salle::class)]
    private ?Salle $salle = null;

    public function __construct()
    {
        parent::__construct();
        // Passez l'objet Role::SPORTIF ici
       $this->setRole(Role::RESPONSABLE_SALLE);  // Définit le rôle comme responsable de salle
    }

    /**
     * Récupère la salle associée au responsable.
     *
     * @return Salle|null
     */
    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    /**
     * Associe une salle au responsable de salle.
     *
     * @param Salle|null $salle
     * @return self
     */
    public function setSalle(?Salle $salle): static
    {
        // Si la salle est dissociée, nous mettons à null le responsable dans la salle
        if ($salle === null && $this->salle !== null) {
            $this->salle->setResponsable(null);
        }

        // Si la salle est assignée, nous nous assurons qu'elle est bien associée au responsable
        if ($salle !== null && $salle->getResponsable() !== $this) {
            $salle->setResponsable($this);
        }

        $this->salle = $salle;
        return $this;
    }
}

>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
