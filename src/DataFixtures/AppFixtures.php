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
        $this->categories = $this->createCategories($manager);

        // Création des vêtements
        $this->createClothingItems($manager, $this->categories);

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
                $this->createOutfits($manager, $user, $allWardrobes[$user->getEmail()], $users);
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
        $profile->setAvatar('default.webp');
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

    private function createCategories(ObjectManager $manager): array
    {
        $categoryNames = [
            'T-shirts',
            'Chemises',
            'Débardeurs',
            'Pulls',
            'Robes',
            'Manteaux',
            'Vestes',
            'Pantalons',
            'Shorts',
            'Jupes',
            'Chaussures',
            'Bottes',
            'Sandales',
            'Accessoires',
            'Ceintures',
            'Écharpes',
            'Maillots de bain',
            'Lingerie',
            'Pyjamas'
        ];

        $categories = [];
        foreach ($categoryNames as $name) {
            $category = new CategoryItem();
            $category->setName($name);
            $manager->persist($category);
            $categories[] = $category;
        }

        return $categories;
    }

    private function createClothingItems(ObjectManager $manager, array $categories): void
    {
        $clothingData = [
            // Hauts
            ['name' => 'T-shirt basique', 'description' => 'T-shirt blanc en coton', 'category' => 'T-shirts', 'brand' => 'Uniqlo', 'color' => 'Blanc', 'imageUrl' => $this->imageUrls['T-shirt basique'][0]],
            ['name' => 'T-shirt imprimé', 'description' => 'T-shirt avec motif', 'category' => 'T-shirts', 'brand' => 'H&M', 'color' => 'Noir', 'imageUrl' => $this->imageUrls['T-shirt imprimé'][0]],
            ['name' => 'Chemise oxford', 'description' => 'Chemise en coton oxford', 'category' => 'Chemises', 'brand' => 'Ralph Lauren', 'color' => 'Bleu', 'imageUrl' => $this->imageUrls['Chemise oxford'][0]],

            // Bas
            ['name' => 'Jean slim', 'description' => 'Jean coupe slim', 'category' => 'Pantalons', 'brand' => 'Levi\'s', 'color' => 'Bleu', 'imageUrl' => $this->imageUrls['Jean slim'][0]],
            ['name' => 'Chino', 'description' => 'Pantalon chino', 'category' => 'Pantalons', 'brand' => 'Dockers', 'color' => 'Beige', 'imageUrl' => $this->imageUrls['Chino'][0]],

            // Chaussures
            ['name' => 'Stan Smith', 'description' => 'Sneakers classiques', 'category' => 'Chaussures', 'brand' => 'Adidas', 'color' => 'Blanc', 'imageUrl' => $this->imageUrls['Stan Smith'][0]],
            ['name' => 'Chelsea boots', 'description' => 'Boots en cuir', 'category' => 'Bottes', 'brand' => 'Church\'s', 'color' => 'Noir', 'imageUrl' => $this->imageUrls['Chelsea boots'][0]]
        ];

        $this->clothingItems = [];
        foreach ($clothingData as $index => $data) {
            $clothingItem = new ClothingItem();
            $clothingItem->setName($data['name']);
            $clothingItem->setDescription($data['description']);
            $clothingItem->setBrand($data['brand']);
            $clothingItem->setColor($data['color']);
            $clothingItem->setCreatedAt(new \DateTimeImmutable());

            // Trouver la catégorie correspondante
            $categoryFound = false;
            foreach ($categories as $category) {
                if (strtolower($category->getName()) === strtolower($data['category'])) {
                    $clothingItem->setCategory($category);
                    $categoryFound = true;
                    break;
                }
            }

            if (!$categoryFound) {
                throw new \Exception(sprintf('Catégorie "%s" non trouvée pour le vêtement "%s"', $data['category'], $data['name']));
            }

            // Télécharger l'image
            if (isset($data['imageUrl'])) {
                $imagePath = $this->downloadAndSaveImage($data['imageUrl'], strtolower(str_replace([' ', '\''], '-', $data['name'])));
                if ($imagePath) {
                    $clothingItem->addImage($imagePath);
                }
            }

            $manager->persist($clothingItem);
            $this->clothingItems[$index] = $clothingItem;
        }
    }

    private function downloadAndSaveImage(string $url, string $name): ?string
    {
        $uploadDir = 'public/uploads/images/';
        $filename = $name . '.webp';
        $filePath = $uploadDir . $filename;

        // Vérifier si le fichier existe déjà
        if (file_exists($filePath)) {
            return 'uploads/images/' . $filename;
        }

        // Créer le répertoire s'il n'existe pas
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $imageData = curl_exec($ch);
            curl_close($ch);

            if ($imageData) {
                file_put_contents($filePath, $imageData);
                return 'uploads/images/' . $filename;
            }
        } catch (\Exception $e) {
            // Log l'erreur ou la gérer selon les besoins
            return null;
        }

        return null;
    }

    private function createWardrobes(ObjectManager $manager, User $user): array
    {
        $wardrobeData = [
            [
                'name' => 'Garde-robe principale',
                'description' => 'Ma garde-robe de tous les jours',
                'imageUrl' => $this->wardrobeImageUrls['Garde-robe principale']
            ],
            [
                'name' => 'Tenues de sport',
                'description' => 'Mes vêtements de sport',
                'imageUrl' => $this->wardrobeImageUrls['Tenues de sport']
            ],
            [
                'name' => 'Tenues de soirée',
                'description' => 'Mes tenues pour les occasions spéciales',
                'imageUrl' => $this->wardrobeImageUrls['Tenues de soirée']
            ],
            [
                'name' => 'Vêtements d\'été',
                'description' => 'Ma collection estivale',
                'imageUrl' => $this->wardrobeImageUrls['Collection été']
            ],
            [
                'name' => 'Vêtements d\'hiver',
                'description' => 'Ma collection hivernale',
                'imageUrl' => $this->wardrobeImageUrls['Tenues de travail']
            ]
        ];

        $wardrobes = [];
        foreach ($wardrobeData as $data) {
            $wardrobe = new Wardrobe();
            $wardrobe->setName($data['name']);
            $wardrobe->setDescription($data['description']);

            // Télécharger l'image
            if (isset($data['imageUrl'])) {
                $imagePath = $this->downloadAndSaveImage($data['imageUrl'], 'wardrobe-' . strtolower(str_replace([' ', '\''], '-', $data['name'])));
                if ($imagePath) {
                    $wardrobe->setImage($imagePath);
                }
            }

            $wardrobe->setAuthor($user);
            $wardrobe->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($wardrobe);
            $wardrobes[] = $wardrobe;
        }

        return $wardrobes;
    }

    private function createOutfits(ObjectManager $manager, User $user, array $wardrobes, array $users): void
    {
        $outfitData = [
            'Garde-robe principale' => [
                [
                    'name' => 'Tenue décontractée',
                    'description' => 'Look casual pour tous les jours',
                    'items' => [0, 3, 5], // T-shirt basique, Jean slim, Stan Smith
                    'imageUrl' => $this->outfitImageUrls['Tenue décontractée'][0],
                    'likes' => 12,
                    'reviews' => [
                        ['content' => 'Super tenue, très confortable !'],
                        ['content' => 'J\'adore le style décontracté']
                    ]
                ],
                [
                    'name' => 'Business casual',
                    'description' => 'Tenue professionnelle décontractée',
                    'items' => [2, 4, 6], // Chemise oxford, Chino, Chelsea boots
                    'imageUrl' => $this->outfitImageUrls['Style business'][0],
                    'likes' => 8,
                    'reviews' => [
                        ['content' => 'Parfait pour le bureau'],
                        ['content' => 'Élégant et professionnel']
                    ]
                ]
            ],
            'Tenues de sport' => [
                [
                    'name' => 'Tenue de running',
                    'description' => 'Pour mes séances de course à pied',
                    'items' => [1, 3, 5], // T-shirt imprimé, Jean slim, Stan Smith
                    'imageUrl' => $this->outfitImageUrls['Look streetwear'][0],
                    'likes' => 15,
                    'reviews' => [
                        ['content' => 'Très confortable pour courir'],
                        ['content' => 'Parfait pour le sport']
                    ]
                ]
            ],
            'Tenues de soirée' => [
                [
                    'name' => 'Tenue de gala',
                    'description' => 'Pour les événements formels',
                    'items' => [2, 4, 6], // Chemise oxford, Chino, Chelsea boots
                    'imageUrl' => $this->outfitImageUrls['Tenue de soirée'][0],
                    'likes' => 20,
                    'reviews' => [
                        ['content' => 'Superbe tenue de soirée'],
                        ['content' => 'Très élégant']
                    ]
                ]
            ]
        ];

        foreach ($wardrobes as $wardrobe) {
            if (isset($outfitData[$wardrobe->getName()])) {
                foreach ($outfitData[$wardrobe->getName()] as $data) {
        $outfit = new Outfit();
                    $outfit->setName($data['name']);
                    $outfit->setDescription($data['description']);
        $outfit->setAuthor($user);
                    $outfit->setWardrobe($wardrobe);
                    $outfit->setCreatedAt(new \DateTimeImmutable());
                    $outfit->setLikesCount(0);
        $outfit->setIsPublished(true);

                    // Télécharger l'image de la tenue
                    if (isset($data['imageUrl'])) {
                        $imagePath = $this->downloadAndSaveImage($data['imageUrl'], 'outfit-' . strtolower(str_replace([' ', '\''], '-', $data['name'])));
                        if ($imagePath) {
                            $outfit->addImage($imagePath);
                        }
                    }

        $manager->persist($outfit);

                    // Ajouter les vêtements à la tenue
                    foreach ($data['items'] as $index) {
                        if (isset($this->clothingItems[$index])) {
        $outfitItem = new OutfitItem();
                            $outfitItem->setClothingItem($this->clothingItems[$index]);
        $outfitItem->setWardrobe($wardrobe);
        $outfitItem->setSize('M');
        $manager->persist($outfitItem);

                            // Ajouter le vêtement à la collection de l'outfit
                            $outfit->addOutfitItem($outfitItem);
                            $outfitItem->addOutfit($outfit);
                        }
                    }

                    // Distribution aléatoire des likes parmi les utilisateurs
                    $potentialLikers = array_filter($users, function ($potentialUser) use ($user) {
                        return $potentialUser !== $user && !in_array('ROLE_ADMIN', $potentialUser->getRoles());
                    });

                    // On prend un nombre aléatoire d'utilisateurs qui vont liker
                    $numberOfLikes = random_int(0, count($potentialLikers));
                    $likesCount = 0;

                    if ($numberOfLikes > 0) {
                        $potentialLikersArray = array_values($potentialLikers);
                        $likerIndices = $numberOfLikes === count($potentialLikers)
                            ? range(0, count($potentialLikers) - 1)
                            : (array) array_rand($potentialLikersArray, $numberOfLikes);

                        foreach ((array) $likerIndices as $likerIndex) {
                            $liker = $potentialLikersArray[$likerIndex];
                            $like = new Like();
                            $like->setOutfit($outfit);
                            $like->setAuthor($liker);
                            $like->setCreatedAt(new \DateTimeImmutable());
                            $manager->persist($like);
                            $likesCount++;
                        }
                    }

                    // Mettre à jour le nombre total de likes
                    $outfit->setLikesCount($likesCount);
                    $manager->persist($outfit);

                    // Ajouter les reviews
                    foreach ($data['reviews'] as $reviewData) {
                        $review = new Review();
                        $review->setOutfit($outfit);
                        $review->setAuthor($user);
                        $review->setContent($reviewData['content']);
                        $review->setCreatedAt(new \DateTimeImmutable());
                        $manager->persist($review);
                    }
                }
            }
        }
    }
}
