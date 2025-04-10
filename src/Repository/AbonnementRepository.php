<?php

namespace App\Repository;

use App\Entity\Abonnement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AbonnementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Abonnement::class);
    }

    public function findBySalle($salle)
    {
        return $this->createQueryBuilder('a')
            ->join('a.salle', 's')
            ->where('s.id = :salleId')
            ->setParameter('salleId', $salle->getId())
            ->getQuery()
            ->getResult();
    }
}