<?php
namespace App\Entity;

use App\Enum\Role;
use Doctrine\ORM\Mapping as ORM;


class Sportif extends User
{
  

    public function __construct()
    {
        parent::__construct();
        // Passez l'objet Role::SPORTIF ici
        $this->setRole(Role::SPORTIF); // Assurez-vous que Role::SPORTIF est un objet de l'énumération Role
    }
}
