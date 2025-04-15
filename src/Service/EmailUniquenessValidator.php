<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class EmailUniquenessValidator
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Méthodes pour vérifier l'unicité de l'email
}
