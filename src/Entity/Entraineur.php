<?php

namespace App\Entity;

use App\Repository\EntraineurRepository;
use Doctrine\ORM\Mapping as ORM;
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
        // Appel du constructeur parent pour initialiser les propriétés héritées de User
        parent::__construct();
        $this->setRole(Role::ENTRAINEUR); // Assurez-vous que Role::ENTRAINEUR est valide
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
