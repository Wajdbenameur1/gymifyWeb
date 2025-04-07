<?php
namespace App\Entity;

use App\Repository\EntraineurRepository;
use Doctrine\ORM\Mapping as ORM;

// On dit à Doctrine que Admin hérite de User
#[ORM\Entity(repositoryClass: EntraineurRepository::class)]
class Entraineur extends User
{
    // Pas besoin d'une nouvelle propriété ici, car Admin hérite déjà des propriétés de User

    public function __construct()
    {
        // Initialise le rôle d'admin
        parent::__construct();
        $this->setRole(Role::ENTRAINEUR); // Assurez-vous que Role::ADMIN existe et est valide
    }

    // Vous pouvez ajouter des méthodes spécifiques à l'Admin si nécessaire.
    // Exemple: getters/setters pour des propriétés spécifiques aux Admins.
}
