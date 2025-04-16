<?php

namespace App\Repository;

use App\Entity\EquipeEvent;
use App\Entity\Salle;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EquipeEvent>
 */
class EquipeEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipeEvent::class);
    }

    /**
     * Find EquipeEvents by Salle and optionally by the logged-in Sportif's Equipe.
     *
     * @param Salle $salle
     * @param User|null $sportif
     * @return EquipeEvent[]
     */
    public function findBySalle(Salle $salle): array
    {
        return $this->createQueryBuilder('ee')
            ->join('ee.event', 'e')
            ->where('e.salle = :salle')
            ->setParameter('salle', $salle)
            ->getQuery()
            ->getResult();
    

        return $qb->getQuery()->getResult();
    }
}