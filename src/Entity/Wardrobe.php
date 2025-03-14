<?php

namespace App\Entity;

use App\Repository\WardrobeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WardrobeRepository::class)]
class Wardrobe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'wardrobes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, OutfitItem>
     */
    #[ORM\OneToMany(targetEntity: OutfitItem::class, mappedBy: 'wardrobe', orphanRemoval: true, cascade: ['remove'])]
    private Collection $outfitItems;

    /**
     * @var Collection<int, Outfit>
     */
    #[ORM\OneToMany(targetEntity: Outfit::class, mappedBy: 'wardrobe', orphanRemoval: true)]
    private Collection $outfits;

    public function __construct()
    {
        $this->outfitItems = new ArrayCollection();
        $this->outfits = new ArrayCollection();
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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
            $outfitItem->setWardrobe($this);
        }

        return $this;
    }

    public function removeOutfitItem(OutfitItem $outfitItem): static
    {
        if ($this->outfitItems->removeElement($outfitItem)) {
            // set the owning side to null (unless already changed)
            if ($outfitItem->getWardrobe() === $this) {
                $outfitItem->setWardrobe(null);
            }
        }

        return $this;
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
            $outfit->setWardrobe($this);
        }
        return $this;
    }

    public function removeOutfit(Outfit $outfit): static
    {
        if ($this->outfits->removeElement($outfit)) {
            // set the owning side to null (unless already changed)
            if ($outfit->getWardrobe() === $this) {
                $outfit->setWardrobe(null);
            }
        }
        return $this;
    }
}
