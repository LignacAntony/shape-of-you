<?php

namespace App\EventListener;

use App\Entity\Like;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: Like::class)]
#[AsEntityListener(event: Events::preRemove, entity: Like::class)]
class LikeListener
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preRemove,
        ];
    }

    public function prePersist(Like $like): void
    {
        $like->setCreatedAt(new \DateTimeImmutable());

        $this->updateOutfitLikesCount($like, 1);
    }

    public function preRemove(Like $like): void
    {
        $this->updateOutfitLikesCount($like, -1);
    }

    private function updateOutfitLikesCount(Like $like, int $increment): void
    {
        $outfit = $like->getOutfit();
        if ($outfit) {
            $currentLikesCount = $outfit->getLikesCount();
            $newLikesCount = max(0, $currentLikesCount + $increment);
            $outfit->setLikesCount($newLikesCount);
        }
    }
}
