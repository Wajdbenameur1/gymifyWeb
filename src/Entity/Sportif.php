<?php

namespace App\Entity;

use App\Enum\Role;
use App\Repository\SportifRepository;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: SportifRepository::class)]
class Sportif extends User
{
    public function __construct()
    {
        parent::__construct();
      $this->setRole(\App\Enum\Role::SPORTIF); // Assurez-vous que Role::SPORTIF est un objet de l'énumération Role
    }
}
