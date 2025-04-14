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
        $this->setRole('responsable_salle'); // Sets role to 'admin', which getRoles() will transform to ROLE_ADMIN
    }
    
} 