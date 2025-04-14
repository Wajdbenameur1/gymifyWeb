<?php
namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;

// On dit à Doctrine que Admin hérite de User
#[ORM\Entity(repositoryClass: AdminRepository::class)]
class Admin extends User
{
    // Pas besoin d'une nouvelle propriété ici, car Admin hérite déjà des propriétés de User

 
        public function __construct()
        {
            parent::setRole('admin');  // Définit le rôle de l'utilisateur comme 'sportif'
        }
    
    // Vous pouvez ajouter des méthodes spécifiques à l'Admin si nécessaire.
    // Exemple: getters/setters pour des propriétés spécifiques aux Admins.
}
