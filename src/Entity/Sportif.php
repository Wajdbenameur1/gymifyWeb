<?php

namespace App\Entity;

use App\Enum\Role;
use App\Repository\SportifRepository;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: SportifRepository::class)]
class Sportif extends User
<<<<<<< HEAD
{
    public function __construct()
    {
        parent::__construct();
        // Passez l'objet Role::SPORTIF ici
        $this->setRole(Role::SPORTIF); // Assurez-vous que Role::SPORTIF est un objet de l'énumération Role
    }
=======

{
  public function __construct()
  {
      parent::__construct();
      $this->setRole(Role::SPORTIF);  // Définir le rôle en utilisant l'Enum
     }
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
}
