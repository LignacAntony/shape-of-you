<?php

namespace App\Entity;

use App\Repository\CategoryItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryItemRepository::class)]
class CategoryItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'categoryItems')]
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: true)]
    private ?self $categoryParent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'categoryParent', cascade: ['remove'], orphanRemoval: true)]
    private Collection $categoryItems;

    /**
     * @var Collection<int, ClothingItem>
     */
    #[ORM\OneToMany(targetEntity: ClothingItem::class, mappedBy: 'category', cascade: ['remove'], orphanRemoval: true)]
    private Collection $clothingItems;

    public function __construct()
    {
        $this->categoryItems = new ArrayCollection();
        $this->clothingItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCategoryParent(): ?self
    {
        return $this->categoryParent;
    }

    public function setCategoryParent(?self $categoryParent): static
    {
        $this->categoryParent = $categoryParent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getCategoryItems(): Collection
    {
        return $this->categoryItems;
    }

    public function addCategoryItem(self $categoryItem): static
    {
        if (!$this->categoryItems->contains($categoryItem)) {
            $this->categoryItems->add($categoryItem);
            $categoryItem->setCategoryParent($this);
        }

        return $this;
    }

    public function removeCategoryItem(self $categoryItem): static
    {
        if ($this->categoryItems->removeElement($categoryItem)) {
            // set the owning side to null (unless already changed)
            if ($categoryItem->getCategoryParent() === $this) {
                $categoryItem->setCategoryParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClothingItem>
     */
    public function getClothingItems(): Collection
    {
        return $this->clothingItems;
    }

    public function addClothingItem(ClothingItem $clothingItem): static
    {
        if (!$this->clothingItems->contains($clothingItem)) {
            $this->clothingItems->add($clothingItem);
            $clothingItem->setCategory($this);
        }

        return $this;
    }

    public function removeClothingItem(ClothingItem $clothingItem): static
    {
        if ($this->clothingItems->removeElement($clothingItem)) {
            // set the owning side to null (unless already changed)
            if ($clothingItem->getCategory() === $this) {
                $clothingItem->setCategory(null);
            }
        }

        return $this;
    }
}
