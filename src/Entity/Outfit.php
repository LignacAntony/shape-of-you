<?php

namespace App\Entity;

use App\Repository\OutfitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OutfitRepository::class)]
class Outfit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'outfits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 4000, nullable: true)]
    #[Assert\Length(
        min: 10,
        max: 4000,
        minMessage: 'Votre description doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Votre description ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $CreatedAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $UpdateDateAt = null;

    #[ORM\Column]
    private ?bool $isPublished = null;

    #[ORM\Column]
    private ?int $likesCount = null;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'outfit', cascade: ['remove'], orphanRemoval: true)]
    private Collection $likes;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'outfit', cascade: ['remove'], orphanRemoval: true)]
    private Collection $reviews;

    /**
     * @var Collection<int, OutfitItem>
     */
    #[ORM\OneToMany(targetEntity: OutfitItem::class, mappedBy: 'outfit', cascade: ['remove'], orphanRemoval: true)]
    private Collection $outfitItems;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->outfitItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): static
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getUpdateDateAt(): ?\DateTimeInterface
    {
        return $this->UpdateDateAt;
    }

    public function setUpdateDateAt(?\DateTimeInterface $UpdateDateAt): static
    {
        $this->UpdateDateAt = $UpdateDateAt;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getLikesCount(): ?int
    {
        return $this->likesCount;
    }

    public function setLikesCount(int $likesCount): static
    {
        $this->likesCount = $likesCount;

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setOutfit($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getOutfit() === $this) {
                $like->setOutfit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setOutfit($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getOutfit() === $this) {
                $review->setOutfit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OutfitItem>
     */
    public function getOutfitItems(): Collection
    {
        return $this->outfitItems;
    }

    public function addOutfitItem(OutfitItem $outfitItem): static
    {
        if (!$this->outfitItems->contains($outfitItem)) {
            $this->outfitItems->add($outfitItem);
            $outfitItem->setOutfit($this);
        }

        return $this;
    }

    public function removeOutfitItem(OutfitItem $outfitItem): static
    {
        if ($this->outfitItems->removeElement($outfitItem)) {
            // set the owning side to null (unless already changed)
            if ($outfitItem->getOutfit() === $this) {
                $outfitItem->setOutfit(null);
            }
        }

        return $this;
    }
}
