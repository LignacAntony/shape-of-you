<?php

namespace App\EventListener;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: Review::class)]
class ReviewListener
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(Review $review): void
    {
        $review->setCreatedAt(new \DateTimeImmutable());
    }
}
