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
    private array $wardrobeImageUrls = [
        'Garde-robe principale' => 'https://images.unsplash.com/photo-1558997519-83ea9252edf8?w=800',
        'Tenues de sport' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800',
        'Tenues de soirée' => 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=800',
        'Tenues de travail' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=800',
        'Collection été' => 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=800'
    ];
    private array $outfitImageUrls = [
        'Tenue décontractée' => [
            // T-shirt basique + Jean slim + Air Force 1
            'https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?w=800',
            'https://images.unsplash.com/photo-1523772721666-22ad3c3b6f90?w=800'
        ],
        'Style business' => [
            // Chemise oxford + Pantalon costume + Derby
            'https://images.unsplash.com/photo-1617127365659-c47fa864d8bc?w=800',
            'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=800'
        ],
        'Look streetwear' => [
            // Sweat capuche + Jean mom + Stan Smith
            'https://images.unsplash.com/photo-1536766820879-059fec98ec0a?w=800',
            'https://images.unsplash.com/photo-1512374382149-233c42b6a83b?w=800'
        ],
        'Tenue de soirée' => [
            // Chemise carreaux + Pantalon costume + Mocassins
            'https://images.unsplash.com/photo-1617127365659-c47fa864d8bc?w=800',
            'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=800'
        ],
        'Style casual chic' => [
            // Pull cachemire + Chino + Baskets montantes
            'https://images.unsplash.com/photo-1516826957135-700dedea698c?w=800',
            'https://images.unsplash.com/photo-1490578474895-699cd4e2cf59?w=800'
        ],
        'Tenue d\'été' => [
            // T-shirt rayé + Short jean + Stan Smith
            'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800',
            'https://images.unsplash.com/photo-1562157873-818bc0726f68?w=800'
        ]
    ];
    private array $imageUrls = [
        // Hauts
        'T-shirt basique' => [
            'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800',
            'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=800'
        ],
        'T-shirt imprimé' => [
            'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=800',
            'https://images.unsplash.com/photo-1529374255404-311a2a4f1fd9?w=800'
        ],
        'T-shirt rayé' => [
            'https://images.unsplash.com/photo-1523381294911-8d3cead13475?w=800'
        ],
        'Chemise oxford' => [
            'https://images.unsplash.com/photo-1602810319428-019690571b5b?w=800'
        ],
        'Chemise à carreaux' => [
            'https://images.unsplash.com/photo-1608030609295-a581b8f46672?w=800'
        ],
        'Pull cachemire' => [
            'https://images.unsplash.com/photo-1614093302611-8efc4de12964?w=800'
        ],
        'Pull col roulé' => [
            'https://images.unsplash.com/photo-1519804270019-39e929a7afb5?w=800'
        ],
        'Sweat à capuche' => [
            'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=800'
        ],
        
        // Bas
        'Jean slim' => [
            'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=800'
        ],
        'Jean mom' => [
            'https://images.unsplash.com/photo-1475178626620-a4d074967452?w=800'
        ],
        'Chino' => [
            'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=800'
        ],
        'Pantalon de costume' => [
            'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=800'
        ],
        'Short en jean' => [
            'https://images.unsplash.com/photo-1591195853828-11db59a44f6b?w=800'
        ],
        'Jupe plissée' => [
            'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=800'
        ],
        
        // Chaussures
        'Air Force 1' => [
            'https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=800'
        ],
        'Stan Smith' => [
            'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=800'
        ],
        'Chuck Taylor' => [
            'https://images.unsplash.com/photo-1494496195158-c3becb4f2475?w=800'
        ],
        'Derby cuir' => [
            'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?w=800'
        ],
        'Mocassins cuir' => [
            'https://images.unsplash.com/photo-1573100925118-870b8efc799d?w=800'
        ],
        'Chelsea boots' => [
            'https://images.unsplash.com/photo-1638247025967-b4e38f787b76?w=800'
        ],
        
        // Accessoires
        'Chaîne fine' => [
            'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=800'
        ],
        'Bracelet jonc' => [
            'https://images.unsplash.com/photo-1611591437281-460bfbe1220a?w=800'
        ],
        'Tote bag' => [
            'https://images.unsplash.com/photo-1614179689702-355944cd0918?w=800'
        ],
        'Sac à dos city' => [
            'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800'
        ],
        'Ceinture classique' => [
            'https://images.unsplash.com/photo-1624222247344-550fb60583dc?w=800'
        ]
    ];

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création des utilisateurs
        $users = [];
        $userEmails = [
            'emma@fashion.fr' => ['ROLE_USER'],
            'lucas@style.fr' => ['ROLE_USER'],
            'sarah@mode.fr' => ['ROLE_USER'],
            'admin@admin.fr' => ['ROLE_ADMIN']
        ];

        foreach ($userEmails as $email => $roles) {
            $users[] = $this->createUser($manager, $email, $roles);
        }

        // Création des catégories
        $this->createCategories($manager);

        // Création des vêtements
        $this->createClothingItems($manager);

        // Création des garde-robes pour chaque utilisateur
        $allWardrobes = [];
        foreach ($users as $user) {
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                $allWardrobes[$user->getEmail()] = $this->createWardrobes($manager, $user);
            }
        }

        // Création des tenues pour chaque utilisateur
        foreach ($users as $user) {
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                $this->createOutfits($manager, $user, $allWardrobes[$user->getEmail()]);
            }
        }

        $manager->flush();
    }

    private function createUser(ObjectManager $manager, string $email, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstname(explode('@', $email)[0]);
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
            'height' => random_int(150, 190),
            'weight' => random_int(45, 90),
            'shoulder' => random_int(35, 50),
            'chest' => random_int(80, 110),
            'waist' => random_int(60, 100),
            'hips' => random_int(80, 110)
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
            // Hauts
            ['T-shirts', 'T-shirt basique', 'H&M', 'Noir', '19.99'],
            ['T-shirts', 'T-shirt imprimé', 'Zara', 'Blanc', '25.99'],
            ['T-shirts', 'T-shirt rayé', 'Uniqlo', 'Bleu/Blanc', '15.99'],
            ['Chemises', 'Chemise oxford', 'Ralph Lauren', 'Bleu ciel', '89.99'],
            ['Chemises', 'Chemise à carreaux', 'Tommy Hilfiger', 'Rouge/Noir', '79.99'],
            ['Pulls', 'Pull cachemire', 'Uniqlo', 'Gris', '79.99'],
            ['Pulls', 'Pull col roulé', 'COS', 'Noir', '69.99'],
            ['Sweats', 'Sweat à capuche', 'Nike', 'Gris chiné', '59.99'],
            
            // Bas
            ['Jeans', 'Jean slim', 'Levis', 'Bleu foncé', '99.99'],
            ['Jeans', 'Jean mom', 'Mango', 'Bleu clair', '49.99'],
            ['Pantalons', 'Chino', 'Dockers', 'Beige', '69.99'],
            ['Pantalons', 'Pantalon de costume', 'Hugo Boss', 'Noir', '129.99'],
            ['Shorts', 'Short en jean', 'Levis', 'Bleu', '59.99'],
            ['Jupes', 'Jupe plissée', 'Zara', 'Noir', '39.99'],
            
            // Chaussures
            ['Baskets basses', 'Air Force 1', 'Nike', 'Blanc', '109.99'],
            ['Baskets basses', 'Stan Smith', 'Adidas', 'Blanc/Vert', '94.99'],
            ['Baskets montantes', 'Chuck Taylor', 'Converse', 'Noir', '79.99'],
            ['Derbies', 'Derby cuir', 'Clarks', 'Noir', '129.99'],
            ['Mocassins', 'Mocassins cuir', 'Tod\'s', 'Marron', '299.99'],
            ['Bottes', 'Chelsea boots', 'Dr Martens', 'Noir', '189.99'],
            
            // Accessoires
            ['Colliers', 'Chaîne fine', 'Pandora', 'Argent', '49.99'],
            ['Bracelets', 'Bracelet jonc', 'Swarovski', 'Or', '79.99'],
            ['Sacs à main', 'Tote bag', 'Michael Kors', 'Marron', '199.99'],
            ['Sacs à dos', 'Sac à dos city', 'Herschel', 'Noir', '89.99'],
            ['Ceintures cuir', 'Ceinture classique', 'Hugo Boss', 'Noir', '79.99']
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

            // Télécharger et associer les images
            if (isset($this->imageUrls[$name])) {
                foreach ($this->imageUrls[$name] as $imageUrl) {
                    $imagePath = $this->downloadAndSaveImage($imageUrl, $name);
                    if ($imagePath) {
                        $item->addImage($imagePath);
                    }
                }
            }

            $manager->persist($item);
            $this->clothingItems[] = $item;
        }
    }

    private function downloadAndSaveImage(string $url, string $name): ?string
    {
        try {
            $uploadDir = 'public/uploads/images/fixtures/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = 'webp';
            $filename = sprintf('%s-%s.%s', uniqid(), strtolower(preg_replace('/[^A-Za-z0-9\-]/', '-', $name)), $extension);
            $filepath = $uploadDir . $filename;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $imageData = curl_exec($ch);
            curl_close($ch);

            if ($imageData) {
                file_put_contents($filepath, $imageData);
                return 'uploads/images/fixtures/' . $filename;
            }
        } catch (\Exception $e) {
            // Gérer silencieusement l'erreur
        }

        return null;
    }

    private function createWardrobes(ObjectManager $manager, User $user): array
    {
        $wardrobeData = [
            'Garde-robe principale' => 'Ma garde-robe de tous les jours',
            'Tenues de sport' => 'Pour mes activités sportives',
            'Tenues de soirée' => 'Pour les occasions spéciales',
            'Tenues de travail' => 'Pour le bureau',
            'Collection été' => 'Mes tenues estivales'
        ];

        $wardrobes = [];
        foreach ($wardrobeData as $name => $description) {
            $wardrobe = new Wardrobe();
            $wardrobe->setAuthor($user);
            $wardrobe->setName($name);
            $wardrobe->setDescription($description);
            $wardrobe->setCreatedAt(new \DateTimeImmutable());

            // Télécharger et associer l'image de la garde-robe
            if (isset($this->wardrobeImageUrls[$name])) {
                $imagePath = $this->downloadAndSaveImage($this->wardrobeImageUrls[$name], 'wardrobe-' . $name);
                if ($imagePath) {
                    $wardrobe->setImage($imagePath);
                }
            }

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
                'items' => [0, 8, 14], // T-shirt basique, Jean slim, Air Force 1
                'wardrobe' => 0
            ],
            [
                'name' => 'Style business',
                'description' => 'Pour une journée au bureau',
                'items' => [3, 11, 17], // Chemise oxford, Pantalon costume, Derby
                'wardrobe' => 3
            ],
            [
                'name' => 'Look streetwear',
                'description' => 'Style urbain et tendance',
                'items' => [7, 9, 15], // Sweat capuche, Jean mom, Stan Smith
                'wardrobe' => 0
            ],
            [
                'name' => 'Tenue de soirée',
                'description' => 'Pour les événements chics',
                'items' => [4, 11, 18], // Chemise carreaux, Pantalon costume, Mocassins
                'wardrobe' => 2
            ],
            [
                'name' => 'Style casual chic',
                'description' => 'Un look élégant mais décontracté',
                'items' => [5, 10, 16], // Pull cachemire, Chino, Baskets montantes
                'wardrobe' => 0
            ],
            [
                'name' => 'Tenue d\'été',
                'description' => 'Parfait pour les journées chaudes',
                'items' => [2, 12, 15], // T-shirt rayé, Short jean, Stan Smith
                'wardrobe' => 4
            ]
        ];

        foreach ($outfitData as $data) {
            $outfit = new Outfit();
            $outfit->setAuthor($user);
            $outfit->setName($data['name']);
            $outfit->setDescription($data['description']);
            $outfit->setIsPublished(true);
            $outfit->setCreatedAt(new \DateTimeImmutable());
            $outfit->setLikesCount(random_int(0, 100));

            // Télécharger et associer les images de la tenue
            if (isset($this->outfitImageUrls[$data['name']])) {
                foreach ($this->outfitImageUrls[$data['name']] as $imageUrl) {
                    $imagePath = $this->downloadAndSaveImage($imageUrl, 'outfit-' . strtolower(preg_replace('/[^A-Za-z0-9\-]/', '-', $data['name'])));
                    if ($imagePath) {
                        $outfit->addImage($imagePath);
                    }
                }
            }

            $manager->persist($outfit);

            // Création des OutfitItems
            foreach ($data['items'] as $itemIndex) {
                $outfitItem = new OutfitItem();
                $outfitItem->setClothingItem($this->clothingItems[$itemIndex]);
                $outfitItem->setWardrobe($wardrobes[$data['wardrobe']]);
                $outfitItem->setSize(['XS', 'S', 'M', 'L', 'XL'][random_int(0, 4)]);
                $outfitItem->setPurchaseAt(new \DateTimeImmutable('-' . random_int(1, 365) . ' days'));
                $outfitItem->addOutfit($outfit);
                $manager->persist($outfitItem);
            }

            // Ajout de likes et reviews
            $numLikes = random_int(5, 20);
            $numReviews = random_int(3, 8);

            for ($i = 0; $i < $numLikes; $i++) {
                $like = new Like();
                $like->setAuthor($user);
                $like->setOutfit($outfit);
                $like->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($like);
            }

            $reviews = [
                'Super tenue, j\'adore le style !',
                'Très belle association de couleurs',
                'Parfait pour toutes les occasions',
                'Cette tenue est vraiment élégante',
                'J\'aime beaucoup l\'association des pièces',
                'Look très tendance',
                'Superbe composition',
                'Style parfaitement maîtrisé'
            ];

            for ($i = 0; $i < $numReviews; $i++) {
                $review = new Review();
                $review->setAuthor($user);
                $review->setOutfit($outfit);
                $review->setContent($reviews[array_rand($reviews)]);
                $review->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($review);
            }
        }
    }
}
