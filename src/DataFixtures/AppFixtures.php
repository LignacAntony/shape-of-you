<?php

namespace App\DataFixtures;
use App\Entity\User;

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
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTime());
        $manager->persist($user);

        $useradmin = new User();
        $useradmin->setEmail('admin@admin.fr');
        $useradmin->setFirstname('John');
        $useradmin->setLastname('Doe');
        $useradmin->setUsername('johndoe');
        $useradmin->setRoles(['ROLE_ADMIN']);
        $useradmin->setVerified(true);
        $hashedPassword = $this->passwordHasher->hashPassword($useradmin, 'password');
        $useradmin->setPassword($hashedPassword);
        $useradmin->setCreatedAt(new \DateTimeImmutable());
        $useradmin->setUpdatedAt(new \DateTime());
        $manager->persist($useradmin);

        $manager->flush();
    }
}
