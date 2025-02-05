<?php

namespace App\EventListener;

use App\Entity\Wardrobe;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: Wardrobe::class)]
class WardrobeListener
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(Wardrobe $wardrobe): void
    {
        $wardrobe->setCreatedAt(new \DateTimeImmutable());
    }
}
