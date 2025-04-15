<?php

namespace App\Entity;
use App\Enum\Role;
use App\Repository\EntraineurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntraineurRepository::class)]
class Entraineur extends User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    // Le constructeur définit le rôle d'Entraîneur
   
            public function __construct()
            {
                parent::__construct();
                $this->setRole(Role::ENTRAINEUR); // Sets role to 'admin', which getRoles() will transform to ROLE_ADMIN
            }
    

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
}
