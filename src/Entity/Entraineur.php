<?php

namespace App\Entity;

use App\Repository\EntraineurRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: EntraineurRepository::class)]
class Entraineur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Entraineur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntraineur(): ?string
    {
        return $this->Entraineur;
    }

    public function setEntraineur(string $Entraineur): static
    {
        $this->Entraineur = $Entraineur;

        return $this;
    }

use App\Enum\Role; // Assurez-vous que Role est bien importé

// On dit à Doctrine que Entraineur hérite de User
#[ORM\Entity(repositoryClass: EntraineurRepository::class)]
class Entraineur extends User
{
    // Définition de la colonne 'specialite' dans la base de données
  // Spécialité de l'entraîneur, optionnelle

    // Le constructeur définit le rôle d'Entraîneur
   
            public function __construct()
            {
                parent::__construct();
                $this->setRole('entraineur'); // Sets role to 'admin', which getRoles() will transform to ROLE_ADMIN
            }
    

    // Getter pour la spécialité
    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(?string $specialite): static
    {
        $this->specialite = $specialite;
        return $this;
    }

    // Méthode spécifique à l'entraîneur si nécessaire, par exemple pour ajouter des comportements ou des validations.

}
