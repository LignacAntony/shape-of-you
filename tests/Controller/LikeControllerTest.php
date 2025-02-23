<?php

namespace App\Tests\Controller;

use App\Entity\Like;
use App\Entity\User;
use App\Entity\Profile;
use App\Entity\Outfit;
use App\Entity\Wardrobe;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LikeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/admin/like';
    private UserPasswordHasherInterface $passwordHasher;
    private User $user;
    private Outfit $outfit;
    private Wardrobe $wardrobe;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Like::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Clean up users first
        $userRepository = $this->manager->getRepository(User::class);
        foreach ($userRepository->findAll() as $user) {
            $this->manager->remove($user);
        }
        $this->manager->flush();

        // Create and login as admin user
        $this->user = new User();
        $this->user->setEmail('admin@test.com');
        $this->user->setRoles(['ROLE_ADMIN']);
        $this->user->setPassword($this->passwordHasher->hashPassword($this->user, 'test123'));

        // Create profile for admin user
        $profile = new Profile();
        $profile->setAppUser($this->user);
        $this->user->setProfile($profile);

        $this->manager->persist($this->user);
        $this->manager->persist($profile);

        // Create a wardrobe
        $this->wardrobe = new Wardrobe();
        $this->wardrobe->setName('Test Wardrobe');
        $this->wardrobe->setDescription('Test Description');
        $this->wardrobe->setAuthor($this->user);
        $this->wardrobe->setCreatedAt(new \DateTimeImmutable());
        $this->manager->persist($this->wardrobe);

        // Create an outfit
        $this->outfit = new Outfit();
        $this->outfit->setName('Test Outfit');
        $this->outfit->setDescription('Test Description');
        $this->outfit->setAuthor($this->user);
        $this->outfit->setWardrobe($this->wardrobe);
        $this->outfit->setCreatedAt(new \DateTimeImmutable());
        $this->outfit->setLikesCount(0);
        $this->outfit->setIsPublished(true);
        $this->manager->persist($this->outfit);

        // Clean up likes
        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
        $this->client->loginUser($this->user);
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Like index');
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%s/new', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Create', [
            'like[author]' => $this->user->getId(),
            'like[outfit]' => $this->outfit->getId(),
        ]);

        self::assertResponseRedirects('/admin/like');

        self::assertSame(1, $this->repository->count([]));

        $like = $this->repository->findAll();
        self::assertSame($this->user->getId(), $like[0]->getAuthor()->getId());
        self::assertSame($this->outfit->getId(), $like[0]->getOutfit()->getId());
    }

    public function testShow(): void
    {
        $fixture = new Like();
        $fixture->setAuthor($this->user);
        $fixture->setOutfit($this->outfit);
        $fixture->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s/%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Like');
    }

    public function testEdit(): void
    {
        $fixture = new Like();
        $fixture->setAuthor($this->user);
        $fixture->setOutfit($this->outfit);
        $fixture->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s/%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'like[author]' => $this->user->getId(),
            'like[outfit]' => $this->outfit->getId(),
        ]);

        self::assertResponseRedirects('/admin/like');

        $fixture = $this->repository->findAll();

        self::assertSame($this->user->getId(), $fixture[0]->getAuthor()->getId());
        self::assertSame($this->outfit->getId(), $fixture[0]->getOutfit()->getId());
    }

    public function testRemove(): void
    {
        $fixture = new Like();
        $fixture->setAuthor($this->user);
        $fixture->setOutfit($this->outfit);
        $fixture->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s/%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/admin/like');
        self::assertSame(0, $this->repository->count([]));
    }
}
