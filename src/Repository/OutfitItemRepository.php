<?php

namespace App\Repository;

use App\Entity\OutfitItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\Outfit;

/**
 * @extends ServiceEntityRepository<OutfitItem>
 */
class OutfitItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OutfitItem::class);
    }

    public function findOutfitItemWithAccessCheck(int $outfitItemId, int $outfitId, User $user): ?OutfitItem
    {
        return $this->createQueryBuilder('oi')
            ->join('oi.outfit', 'o')
            ->andWhere('oi.id = :outfitItemId')
            ->andWhere('o.id = :outfitId')
            ->andWhere('o.author = :user')
            ->setParameter('outfitItemId', $outfitItemId)
            ->setParameter('outfitId', $outfitId)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOutfitItemsByOutfit(Outfit $outfit): array
    {
        return $this->createQueryBuilder('oi')
            ->join('oi.outfits', 'o')
            ->andWhere('o = :outfit')
            ->setParameter('outfit', $outfit)
            ->getQuery()
            ->getResult();
    }
}
