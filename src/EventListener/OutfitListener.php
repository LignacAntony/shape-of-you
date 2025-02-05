<?php

namespace App\EventListener;

use App\Entity\Outfit;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: Outfit::class)]
class OutfitListener
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(Outfit $outfit): void
    {
        $outfit->setCreatedAt(new \DateTimeImmutable());
        $outfit->setLikesCount(0);
    }
}
