<?php

namespace App\Tests\Controller;

use App\Entity\Outfit;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\Profile;
use App\Entity\Wardrobe;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ReviewControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private User $user;
    private Outfit $outfit;
    private string $path = '/admin/review/';
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Review::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Clean up the database
        $this->manager->createQuery('DELETE FROM App\Entity\Review')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\OutfitItem')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Outfit')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Wardrobe')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Profile')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\User')->execute();

        $this->user = new User();
        $this->user->setEmail('review-test-user@test.fr');
        $this->user->setFirstname('John');
        $this->user->setLastname('Doe');
        $this->user->setUsername('johndoe');
        $this->user->setVerified(true);
        $hashedUserPassword = $this->passwordHasher->hashPassword($this->user, 'password');
        $this->user->setPassword($hashedUserPassword);
        $this->user->setCreatedAt(new \DateTimeImmutable());
        $this->user->setUpdatedAt(new \DateTime());
        $this->user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        // Create profile for user
        $profile = new Profile();
        $profile->setAppUser($this->user);
        $this->user->setProfile($profile);

        $this->manager->persist($this->user);
        $this->manager->persist($profile);

        // Create wardrobe
        $wardrobe = new Wardrobe();
        $wardrobe->setName('Test Wardrobe');
        $wardrobe->setDescription('Test Description');
        $wardrobe->setAuthor($this->user);
        $wardrobe->setCreatedAt(new \DateTimeImmutable());
        $this->manager->persist($wardrobe);

        $this->outfit = new Outfit();
        $this->outfit->setAuthor($this->user);
        $this->outfit->setWardrobe($wardrobe);
        $this->outfit->setName('Jogging outfit');
        $this->outfit->setDescription('Mon meilleur outfit pour aller au kebab !');
        $this->outfit->setIsPublished(true);
        $this->outfit->setCreatedAt(new \DateTimeImmutable());
        $this->outfit->setUpdateDateAt(new \DateTime());
        $this->outfit->setLikesCount(0);
        $this->manager->persist($this->outfit);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
        $this->client->loginUser($this->user);
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Review index');
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Create', [
            'review[content]' => 'Test Review Content',
            'review[author]' => $this->user->getId(),
            'review[outfit]' => $this->outfit->getId(),
        ]);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $fixture = new Review();
        $fixture->setContent('Test Review Content');
        $fixture->setAuthor($this->user);
        $fixture->setOutfit($this->outfit);
        $fixture->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Review');
    }

    public function testEdit(): void
    {
        $fixture = new Review();
        $fixture->setContent('Original Content');
        $fixture->setAuthor($this->user);
        $fixture->setOutfit($this->outfit);
        $fixture->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'review[content]' => 'Updated Content',
            'review[author]' => $this->user->getId(),
            'review[outfit]' => $this->outfit->getId(),
        ]);

        $updatedReview = $this->repository->findOneBy(['id' => $fixture->getId()]);
        self::assertSame('Updated Content', $updatedReview->getContent());
    }

    public function testRemove(): void
    {
        $fixture = new Review();
        $fixture->setContent('Test Review Content');
        $fixture->setAuthor($this->user);
        $fixture->setOutfit($this->outfit);
        $fixture->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame(0, $this->repository->count([]));
    }
}
