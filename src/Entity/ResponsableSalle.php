<?php

namespace App\Entity;

use App\Enum\Role;
use App\Repository\ResponsableSalleRepository;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity(repositoryClass: ResponsableSalleRepository::class)]
class ResponsableSalle extends User
{
    public function __construct()
    {
        parent::__construct();
        // Passez l'objet Role::SPORTIF ici
        $this->setRole(\App\Enum\Role::RESPONSABLESALLE); // Assurez-vous que Role::SPORTIF est un objet de l'énumération Role
    }
} 