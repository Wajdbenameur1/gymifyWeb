<?php

namespace App\Repository;
<<<<<<< HEAD

=======
use App\Entity\Salle;
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
use App\Entity\Abonnement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

<<<<<<< HEAD
/**
 * @extends ServiceEntityRepository<Abonnement>
 */
=======
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
class AbonnementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Abonnement::class);
    }

<<<<<<< HEAD
//    /**
//     * @return Abonnement[] Returns an array of Abonnement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Abonnement
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
=======
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
>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
