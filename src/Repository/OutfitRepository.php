<?php

namespace App\Repository;

use App\Entity\Outfit;
use App\Entity\User;
use App\Entity\Wardrobe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Outfit>
 */
class OutfitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Outfit::class);
    }

    public function findOutfitWithAccessCheck(int $id, ?UserInterface $user): ?Outfit
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->andWhere('o.author = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOutfitsForWardrobe(Wardrobe $wardrobe, ?UserInterface $user): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.wardrobe = :wardrobe')
            ->andWhere('o.author = :user')
            ->setParameter('wardrobe', $wardrobe)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findOutfitWithPublicAccess(int $id, ?UserInterface $user): ?Outfit
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->andWhere('o.author = :user OR (o.isPublished = true)')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPublishedOutfitsByUser(?UserInterface $user): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.author = :user')
            ->andWhere('o.isPublished = true')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function countAllOutfits(): int
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function findTopAuthor(): ?array
    {
        return $this->createQueryBuilder('o')
            ->select('a.id as id, a.email as email, COUNT(o.id) as outfitCount')
            ->innerJoin('o.author', 'a')
            ->groupBy('a.id')
            ->orderBy('outfitCount', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findOutfitWithMostLikes(): ?Outfit
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.likesCount', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
