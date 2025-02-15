<?php

namespace App\Entity;

use App\Repository\OutfitItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OutfitItemRepository::class)]
class OutfitItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Outfit::class, mappedBy: 'outfitItems')]
    private Collection $outfits;

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

    public function __construct()
    {
        $this->outfits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Outfit>
     */
    public function getOutfits(): Collection
    {
        return $this->outfits;
    }

    public function addOutfit(Outfit $outfit): static
    {
        if (!$this->outfits->contains($outfit)) {
            $this->outfits->add($outfit);
            $outfit->addOutfitItem($this);
        }

        return $this;
    }

    public function removeOutfit(Outfit $outfit): static
    {
        if ($this->outfits->removeElement($outfit)) {
            $outfit->removeOutfitItem($this);
        }

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
