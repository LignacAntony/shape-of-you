<?php

namespace App\Repository;

use App\Entity\OutfitItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\Outfit;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * @extends ServiceEntityRepository<OutfitItem>
 */
class OutfitItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OutfitItem::class);
    }

    public function findOutfitItemWithAccessCheck(int $outfitItemId, int $outfitId, ?UserInterface $user): ?OutfitItem
    {
        return $this->createQueryBuilder('oi')
            ->andWhere('oi.id = :outfitItemId')
            ->join('oi.outfits', 'o')
            ->andWhere('o.id = :outfit')
            ->andWhere('o.author = :user')
            ->setParameter('outfitItemId', $outfitItemId)
            ->setParameter('outfit', $outfitId)
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

    public function findOutfitItemsByUser(?UserInterface $user): array
    {
        return $this->createQueryBuilder('oi')
            ->join('oi.wardrobe', 'w')
            ->andWhere('w.author = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
