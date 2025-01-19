<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Profile;
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
        // ---- 1) Créer un user ----
        $user = new User();
        $user->setEmail('user@user.fr');
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setUsername('johndoe');
        $user->setVerified(true);

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
        $user->setPassword($hashedPassword);

        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTime());

        $manager->persist($user);

        // ---- 2) Créer le profile du user ----
        $profile = new Profile();
        $profile->setAppUser($user); // Liaison entre Profile et User
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

        // ---- 3) Flush ----
        $manager->flush();
    }
}
