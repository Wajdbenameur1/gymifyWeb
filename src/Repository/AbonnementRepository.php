<?php

namespace App\Repository;
use App\Entity\Salle;
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
    public function findByFilters(Salle $salle, ?string $type = null, $activityId = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.salle = :salle')
            ->setParameter('salle', $salle);
        
        if ($type) {
            $qb->andWhere('a.type = :type')
               ->setParameter('type', $type);
        }
        
        if ($activityId && is_numeric($activityId)) {
            $qb->join('a.activite', 'act')
               ->andWhere('act.id = :activityId')
               ->setParameter('activityId', (int)$activityId);
        }
        
        return $qb->getQuery()->getResult();
    }
}