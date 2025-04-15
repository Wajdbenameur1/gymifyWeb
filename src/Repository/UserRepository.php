<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\Role;
/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function findAll(): array
    {
        return $this->findBy([], ['id' => 'ASC']);
    }
    // src/Repository/UserRepository.php
public function findByRoleFilter(array $roles): array
{
    return $this->createQueryBuilder('u')
        ->where('u.role IN (:roles)')
        ->setParameter('roles', $roles)
        ->getQuery()
        ->getResult();
}

    public function findByAllowedRoles(array $roles): array
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where($qb->expr()->in('u.role', ':roles'))
           ->setParameter('roles', array_map(fn($r) => $r->value, $roles));

        return $qb->getQuery()->getResult();
    }
    public function findByRole(array $roles): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.role IN (:roles)')
            ->setParameter('roles', $roles)
            ->getQuery()
            ->getResult();
    }
}
