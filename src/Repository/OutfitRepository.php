<?php

namespace App\Repository;

use App\Entity\Outfit;
use App\Entity\User;
use App\Entity\Wardrobe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Outfit>
 */
class OutfitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Outfit::class);
    }

    public function findOutfitWithAccessCheck(int $id, User $user): ?Outfit
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->andWhere('o.author = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOutfitsForWardrobe(Wardrobe $wardrobe, User $user): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.wardrobe = :wardrobe')
            ->andWhere('o.author = :user')
            ->setParameter('wardrobe', $wardrobe)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findOutfitWithPublicAccess(int $id, User $user): ?Outfit
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->andWhere('o.author = :user OR (o.isPublished = true)')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPublishedOutfitsByUser(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.author = :user')
            ->andWhere('o.isPublished = true')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
