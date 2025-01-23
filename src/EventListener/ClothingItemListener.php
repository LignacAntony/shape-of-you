<?php

namespace App\EventListener;

use App\Entity\ClothingItem;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: ClothingItem::class)]
class ClothingItemListener
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(ClothingItem $clothingItem): void
    {
        $clothingItem->setCreatedAt(new \DateTimeImmutable());
    }
}
