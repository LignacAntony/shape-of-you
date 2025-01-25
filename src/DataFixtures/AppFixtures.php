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

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@user.fr');
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setUsername('johndoe');
        $user->setVerified(true);
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        $profile = new Profile();
        $profile->setAppUser($user);
        $profile->setAvatar('https://example.com/avatars/johndoe.png');
        $profile->setBio('Biographie de John Doe...');
        $profile->setPreferences([
            'theme' => 'dark',
        ]);
        $profile->setMeasurements([
            'height' => 180,
            'weight' => 75
        ]);
        $profile->setLastLoginAt(new \DateTime());
        $manager->persist($profile);

        $useradmin = new User();
        $useradmin->setEmail('admin@admin.fr');
        $useradmin->setFirstname('John');
        $useradmin->setLastname('Doe');
        $useradmin->setUsername('johndoe');
        $useradmin->setRoles(['ROLE_ADMIN']);
        $useradmin->setVerified(true);
        $hashedPassword = $this->passwordHasher->hashPassword($useradmin, 'password');
        $useradmin->setPassword($hashedPassword);
        $manager->persist($useradmin);

        $outfit = new Outfit();
        $outfit->setAuthor($user);
        $outfit->setName('Jogging outfit');
        $outfit->setDescription('Mon meilleur outfit pour aller au kebab !');
        $outfit->setIsPublished(true);
        $manager->persist($outfit);

        $like = new Like();
        $like->setAuthor($user);
        $like->setOutfit($outfit);
        $manager->persist($like);

        $review = new Review();
        $review->setAuthor($user);
        $review->setOutfit($outfit);
        $review->setContent('Super outfit !');
        $manager->persist($review);

        $categoryItem = new CategoryItem();
        $categoryItem->setName('Vêtements');
        $categoryItem->setDesription('Catégorie des vêtements');
        $manager->persist($categoryItem);

        $categoryItem2 = new CategoryItem();
        $categoryItem2->setName('Chaussures');
        $categoryItem2->setDesription('Catégorie des chaussures');
        $categoryItem2->setCategoryParent($categoryItem);
        $manager->persist($categoryItem2);

        $categoryItem3 = new CategoryItem();
        $categoryItem3->setName('Baskets');
        $categoryItem3->setDesription('Catégorie des baskets');
        $categoryItem3->setCategoryParent($categoryItem2);
        $manager->persist($categoryItem3);

        $wardrobe = new Wardrobe();
        $wardrobe->setAuthor($user);
        $wardrobe->setName('Armoire de John Doe');
        $wardrobe->setDescription('Armoire de John Doe');
        $manager->persist($wardrobe);

        $clothingItem = new ClothingItem();
        $clothingItem->setCategory($categoryItem3);
        $clothingItem->setName('Nike Air Max 90');
        $clothingItem->setDescription('Les meilleures baskets du monde !');
        $clothingItem->setPrice('120');
        $clothingItem->setColor('Blanc');
        $clothingItem->setBrand('Nike');
        $manager->persist($clothingItem);

        $outfitItem = new OutfitItem();
        $outfitItem->setOutfit($outfit);
        $outfitItem->setClothingItem($clothingItem);
        $outfitItem->setWardrobe($wardrobe);
        $outfitItem->setSize('M');
        $outfitItem->setPurchaseAt(new \DateTimeImmutable());
        $manager->persist($outfitItem);

        $manager->flush();
    }
}
