<?php

namespace App\Entity;

<<<<<<< HEAD
use App\Repository\AbonnementRepository;
use Doctrine\DBAL\Types\Types;
=======
use App\Enum\TypeAbonnement;
use App\Repository\AbonnementRepository;
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
class Abonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
<<<<<<< HEAD
    #[ORM\Column]
    private ?int $id_Abonnement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_Debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_Fin = null;

    #[ORM\Column(length: 255)]
    private ?string $type_Abonnement = null;
=======
    #[ORM\Column(name: 'id_Abonnement')]
    private ?int $id = null;

    #[ORM\Column(name: 'type_abonnement', type: 'string', enumType: TypeAbonnement::class)]
    private ?TypeAbonnement $type = null;
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc

    #[ORM\Column]
    private ?float $tarif = null;

<<<<<<< HEAD
    #[ORM\ManyToOne(inversedBy: 'id_activite')]
    private ?Activité $activite = null;

=======
    #[ORM\ManyToOne(targetEntity: Activité::class, inversedBy: 'abonnements')]
    #[ORM\JoinColumn(name: 'activite_id', referencedColumnName: 'id')]
    private ?Activité $activite = null;

    #[ORM\ManyToOne(targetEntity: Salle::class)]
    #[ORM\JoinColumn(name: 'id_salle', referencedColumnName: 'id')]
    private ?Salle $salle = null;

>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
    public function getId(): ?int
    {
        return $this->id;
    }

<<<<<<< HEAD
    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_Debut;
    }

    public function setDateDebut(\DateTimeInterface $date_Debut): static
    {
        $this->date_Debut = $date_Debut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_Fin;
    }

    public function setDateFin(\DateTimeInterface $date_Fin): static
    {
        $this->date_Fin = $date_Fin;

        return $this;
    }

    public function getTypeAbonnement(): ?string
    {
        return $this->type_Abonnement;
    }

    public function setTypeAbonnement(string $type_Abonnement): static
    {
        $this->type_Abonnement = $type_Abonnement;

=======
    public function getType(): ?TypeAbonnement
    {
        return $this->type;
    }

    public function setType(TypeAbonnement $type): static
    {
        $this->type = $type;
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
        return $this;
    }

    public function getTarif(): ?float
    {
        return $this->tarif;
    }

    public function setTarif(float $tarif): static
    {
        $this->tarif = $tarif;
<<<<<<< HEAD

=======
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
        return $this;
    }

    public function getActivite(): ?Activité
    {
        return $this->activite;
    }

    public function setActivite(?Activité $activite): static
    {
        $this->activite = $activite;
<<<<<<< HEAD

        return $this;
    }
}
=======
        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): static
    {
        $this->salle = $salle;
        return $this;
    }
}
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
