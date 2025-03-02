<?php

namespace App\Repository;

use App\Entity\CategoryItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryItem>
 */
class CategoryItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryItem::class);
    }

    public function countClothingItemsPerCategory(): array
    {
        return $this->createQueryBuilder('cat')
            ->select('cat.name as name, COUNT(ci.id) as count')
            ->leftJoin('cat.clothingItems', 'ci')
            ->groupBy('cat.id')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return CategoryItem[] Returns an array of CategoryItem objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CategoryItem
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
