<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * Find products with low stock (less than or equal to minimum stock level)
     */
    public function findLowStock(int $minimumStock = 5): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stock <= :val')
            ->setParameter('val', $minimumStock)
            ->orderBy('p.stock', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 