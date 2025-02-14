<?php

namespace App\DataFixtures;

use App\Entity\CategoryItem;
use App\Entity\ClothingItem;
use App\Entity\Like;
use App\Entity\Outfit;
use App\Entity\OutfitItem;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\Profile;
use App\Entity\Wardrobe;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private array $categories = [];
    private array $clothingItems = [];

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création des utilisateurs
        $user = $this->createUser($manager, 'user@user.fr', ['ROLE_USER']);
        $admin = $this->createUser($manager, 'admin@admin.fr', ['ROLE_ADMIN']);

        // Création des catégories
        $this->createCategories($manager);

        // Création des vêtements
        $this->createClothingItems($manager);

        // Création des garde-robes pour l'utilisateur
        $wardrobes = $this->createWardrobes($manager, $user);

        // Création des tenues et leurs éléments
        $this->createOutfits($manager, $user, $wardrobes);

        $manager->flush();
    }

    private function createUser(ObjectManager $manager, string $email, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setUsername(explode('@', $email)[0]);
        $user->setRoles($roles);
        $user->setVerified(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        $profile = new Profile();
        $profile->setAppUser($user);
        $profile->setAvatar('default.jpg');
        $profile->setBio('Passionné(e) de mode et de style.');
        $profile->setPreferences([
            'theme' => 'light',
            'notifications' => true
        ]);
        $profile->setMeasurements([
            'height' => 175,
            'weight' => 70,
            'shoulder' => 45,
            'chest' => 95,
            'waist' => 80,
            'hips' => 90
        ]);
        $profile->setLastLoginAt(new \DateTime());
        $manager->persist($profile);

        return $user;
    }

    private function createCategories(ObjectManager $manager): void
    {
        $mainCategories = [
            'Vêtements' => [
                'Hauts' => ['T-shirts', 'Chemises', 'Pulls', 'Sweats'],
                'Bas' => ['Pantalons', 'Jeans', 'Shorts', 'Jupes'],
                'Vestes' => ['Blazers', 'Manteaux', 'Blousons']
            ],
            'Chaussures' => [
                'Sneakers' => ['Baskets basses', 'Baskets montantes'],
                'Chaussures ville' => ['Derbies', 'Mocassins'],
                'Bottes' => ['Chelsea boots', 'Bottines']
            ],
            'Accessoires' => [
                'Bijoux' => ['Colliers', 'Bracelets', 'Bagues'],
                'Sacs' => ['Sacs à main', 'Sacs à dos', 'Pochettes'],
                'Ceintures' => ['Ceintures cuir', 'Ceintures tissu']
            ]
        ];

        foreach ($mainCategories as $mainName => $subCategories) {
            $mainCategory = new CategoryItem();
            $mainCategory->setName($mainName);
            $mainCategory->setDescription("Catégorie $mainName");
            $manager->persist($mainCategory);
            $this->categories[$mainName] = $mainCategory;

            foreach ($subCategories as $subName => $items) {
                $subCategory = new CategoryItem();
                $subCategory->setName($subName);
                $subCategory->setDescription("Sous-catégorie $subName");
                $subCategory->setCategoryParent($mainCategory);
                $manager->persist($subCategory);
                $this->categories[$subName] = $subCategory;

                foreach ($items as $itemName) {
                    $itemCategory = new CategoryItem();
                    $itemCategory->setName($itemName);
                    $itemCategory->setDescription("Type $itemName");
                    $itemCategory->setCategoryParent($subCategory);
                    $manager->persist($itemCategory);
                    $this->categories[$itemName] = $itemCategory;
                }
            }
        }
    }

    private function createClothingItems(ObjectManager $manager): void
    {
        $items = [
            ['T-shirts', 'T-shirt basique', 'H&M', 'Noir', '19.99'],
            ['T-shirts', 'T-shirt imprimé', 'Zara', 'Blanc', '25.99'],
            ['Chemises', 'Chemise oxford', 'Ralph Lauren', 'Bleu ciel', '89.99'],
            ['Pulls', 'Pull cachemire', 'Uniqlo', 'Gris', '79.99'],
            ['Jeans', 'Jean slim', 'Levis', 'Bleu foncé', '99.99'],
            ['Pantalons', 'Chino', 'DockerS', 'Beige', '69.99'],
            ['Baskets basses', 'Air Force 1', 'Nike', 'Blanc', '109.99'],
            ['Baskets basses', 'Stan Smith', 'Adidas', 'Blanc/Vert', '94.99'],
            ['Derbies', 'Derby cuir', 'Clarks', 'Noir', '129.99'],
            ['Colliers', 'Chaîne fine', 'Pandora', 'Argent', '49.99'],
            ['Sacs à main', 'Tote bag', 'Michael Kors', 'Marron', '199.99']
        ];

        foreach ($items as [$category, $name, $brand, $color, $price]) {
            $item = new ClothingItem();
            $item->setCategory($this->categories[$category]);
            $item->setName($name);
            $item->setDescription("Superbe $name de la marque $brand");
            $item->setBrand($brand);
            $item->setColor($color);
            $item->setPrice($price);
            $item->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($item);
            $this->clothingItems[] = $item;
        }
    }

    private function createWardrobes(ObjectManager $manager, User $user): array
    {
        $wardrobeNames = [
            'Garde-robe principale' => 'Ma garde-robe de tous les jours',
            'Tenues de sport' => 'Pour mes activités sportives',
            'Tenues de soirée' => 'Pour les occasions spéciales'
        ];

        $wardrobes = [];
        foreach ($wardrobeNames as $name => $description) {
            $wardrobe = new Wardrobe();
            $wardrobe->setAuthor($user);
            $wardrobe->setName($name);
            $wardrobe->setDescription($description);
            $wardrobe->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($wardrobe);
            $wardrobes[] = $wardrobe;
        }

        return $wardrobes;
    }

    private function createOutfits(ObjectManager $manager, User $user, array $wardrobes): void
    {
        $outfitData = [
            [
                'name' => 'Tenue décontractée',
                'description' => 'Parfait pour une journée détendue',
                'items' => [0, 4, 6] // Indices des clothingItems
            ],
            [
                'name' => 'Style business',
                'description' => 'Pour une journée au bureau',
                'items' => [2, 5, 8]
            ],
            [
                'name' => 'Look streetwear',
                'description' => 'Style urbain et tendance',
                'items' => [1, 4, 7]
            ]
        ];

        foreach ($outfitData as $data) {
        $outfit = new Outfit();
        $outfit->setAuthor($user);
            $outfit->setName($data['name']);
            $outfit->setDescription($data['description']);
        $outfit->setIsPublished(true);
            $outfit->setCreatedAt(new \DateTimeImmutable());
            $outfit->setLikesCount(random_int(0, 50));
        $manager->persist($outfit);

            // Création des OutfitItems
            foreach ($data['items'] as $itemIndex) {
                $outfitItem = new OutfitItem();
                $outfitItem->setOutfit($outfit);
                $outfitItem->setClothingItem($this->clothingItems[$itemIndex]);
                $outfitItem->setWardrobe($wardrobes[0]);
                $outfitItem->setSize(['XS', 'S', 'M', 'L', 'XL'][random_int(0, 4)]);
                $outfitItem->setPurchaseAt(new \DateTimeImmutable('-' . random_int(1, 365) . ' days'));
                $manager->persist($outfitItem);
            }

            // Ajout de quelques likes et reviews
            for ($i = 0; $i < random_int(1, 5); $i++) {
        $like = new Like();
        $like->setAuthor($user);
        $like->setOutfit($outfit);
                $like->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($like);

        $review = new Review();
        $review->setAuthor($user);
        $review->setOutfit($outfit);
                $review->setContent('Super tenue, j\'adore le style !');
                $review->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($review);
            }
        }
    }
}
