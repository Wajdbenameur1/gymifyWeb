<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Sportif extends User
{
    #[ORM\Column(length: 50)]
    private ?string $niveau = null;

    public function __construct()
    {
        parent::__construct();
        $this->setRole(Role::SPORTIF);
    }

}
