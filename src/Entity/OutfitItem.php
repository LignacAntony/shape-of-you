<?php

namespace App\Entity;

use App\Repository\OutfitItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OutfitItemRepository::class)]
class OutfitItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'outfitItems')]
    private ?Outfit $outfit = null;

    #[ORM\ManyToOne(inversedBy: 'outfitItems')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ClothingItem $clothingItem = null;

    #[ORM\ManyToOne(inversedBy: 'outfitItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Wardrobe $wardrobe = null;

    #[ORM\Column(length: 10)]
    private ?string $size = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $purchaseAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOutfit(): ?Outfit
    {
        return $this->outfit;
    }

    public function setOutfit(?Outfit $outfit): static
    {
        $this->outfit = $outfit;

        return $this;
    }

    public function getClothingItem(): ?ClothingItem
    {
        return $this->clothingItem;
    }

    public function setClothingItem(?ClothingItem $clothingItem): static
    {
        $this->clothingItem = $clothingItem;

        return $this;
    }

    public function getWardrobe(): ?Wardrobe
    {
        return $this->wardrobe;
    }

    public function setWardrobe(?Wardrobe $wardrobe): static
    {
        $this->wardrobe = $wardrobe;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getPurchaseAt(): ?\DateTimeImmutable
    {
        return $this->purchaseAt;
    }

    public function setPurchaseAt(?\DateTimeImmutable $purchaseAt): static
    {
        $this->purchaseAt = $purchaseAt;

        return $this;
    }
}
