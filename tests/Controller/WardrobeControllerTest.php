<?php

namespace App\Tests\Controller;

use App\Entity\CategoryItem;
use App\Entity\User;
use App\Entity\Wardrobe;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Profile;

final class WardrobeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private User $user;
    private string $path = '/admin/wardrobe/';
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Wardrobe::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Clean up the database
        $this->manager->createQuery('DELETE FROM App\Entity\OutfitItem')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Outfit')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Wardrobe')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Profile')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\User')->execute();

        // Create user
        $this->user = new User();
        $this->user->setEmail('wardrobe-test@test.fr');
        $this->user->setFirstname('John');
        $this->user->setLastname('Doe');
        $this->user->setUsername('johndoe');
        $this->user->setVerified(true);
        $this->user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($this->user, 'password');
        $this->user->setPassword($hashedPassword);
        $this->user->setCreatedAt(new \DateTimeImmutable());
        $this->user->setUpdatedAt(new \DateTime());

        // Create profile for user
        $profile = new Profile();
        $profile->setAppUser($this->user);
        $this->user->setProfile($profile);

        $this->manager->persist($this->user);
        $this->manager->persist($profile);
        $this->manager->flush();

        $this->client->loginUser($this->user);
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Wardrobe index');
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Create', [
            'wardrobe[name]' => 'Test Wardrobe',
            'wardrobe[description]' => 'Test Description',
        ]);

        self::assertSame(1, $this->repository->count([]));

        $wardrobe = $this->repository->findOneBy(['name' => 'Test Wardrobe']);
        self::assertNotNull($wardrobe);
        self::assertSame('Test Description', $wardrobe->getDescription());
    }

    public function testShow(): void
    {
        $fixture = new Wardrobe();
        $fixture->setName('Test Wardrobe');
        $fixture->setDescription('Test Description');
        $fixture->setAuthor($this->user);
        $fixture->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Wardrobe');
    }

    public function testEdit(): void
    {
        $fixture = new Wardrobe();
        $fixture->setName('Test Wardrobe');
        $fixture->setDescription('Test Description');
        $fixture->setAuthor($this->user);
        $fixture->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Update', [
            'wardrobe[name]' => 'Updated Wardrobe',
            'wardrobe[description]' => 'Updated Description',
        ]);


        $updatedWardrobe = $this->repository->findOneBy(['id' => $fixture->getId()]);
        self::assertSame('Updated Wardrobe', $updatedWardrobe->getName());
        self::assertSame('Updated Description', $updatedWardrobe->getDescription());
    }

    public function testRemove(): void
    {
        $fixture = new Wardrobe();
        $fixture->setName('Test Wardrobe');
        $fixture->setDescription('Test Description');
        $fixture->setAuthor($this->user);
        $fixture->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame(0, $this->repository->count([]));
    }
}
