<?php

namespace App\Entity;

use App\Enum\Role;
use App\Repository\ResponsableSalleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResponsableSalleRepository::class)]
class ResponsableSalle extends User
{
    #[ORM\OneToOne(mappedBy: 'responsable', targetEntity: Salle::class)]
    private ?Salle $salle = null;

    public function __construct()
    {
        parent::__construct();
        $this->setRole(Role::RESPONSABLESALLE);
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): static
    {
        // unset the owning side of the relation if necessary
        if ($salle === null && $this->salle !== null) {
            $this->salle->setResponsable(null);
        }

        // set the owning side of the relation if necessary
        if ($salle !== null && $salle->getResponsable() !== $this) {
            $salle->setResponsable($this);
        }

        $this->salle = $salle;
        return $this;
    }
}