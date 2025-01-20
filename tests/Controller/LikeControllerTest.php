<?php

namespace App\Tests\Controller;

use App\Entity\Like;
use App\Entity\Outfit;
use App\Entity\User;
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
    private User $user;
    private Outfit $outfit;
    private UserPasswordHasherInterface $passwordHasher;
    private string $path = '/admin/like/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Like::class);

        $this->user = new User();
        $this->user->setEmail('user@test.fr');
        $this->user->setFirstname('John');
        $this->user->setLastname('Doe');
        $this->user->setUsername('johndoe');
        $this->user->setVerified(true);
        $hashedUserPassword = $this->passwordHasher->hashPassword($this->user, 'password');
        $this->user->setPassword($hashedUserPassword);
        $this->user->setCreatedAt(new \DateTimeImmutable());
        $this->user->setUpdatedAt(new \DateTime());
        $this->manager->persist($this->user);

        $this->outfit = new Outfit();
        $this->outfit->setAuthor($this->user);
        $this->outfit->setName('Jogging outfit');
        $this->outfit->setDescription('Mon meilleur outfit pour aller au kebab !');
        $this->outfit->setIsPublished(true);
        $this->manager->persist($this->outfit);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Like index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'like[createdAt]' => 'Testing',
            'like[author]' => 'Testing',
            'like[outfit]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Like();
        $fixture->setAuthor($this->user);
        $fixture->setOutfit($this->outfit);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Like');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Like();
        $fixture->setAuthor($this->user);
        $fixture->setOutfit($this->outfit);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'like[createdAt]' => 'Something New',
            'like[author]' => 'Something New',
            'like[outfit]' => 'Something New',
        ]);

        self::assertResponseRedirects('/admin/like/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getAuthor());
        self::assertSame('Something New', $fixture[0]->getOutfit());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Like();
        $fixture->setAuthor($this->user);
        $fixture->setOutfit($this->outfit);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/admin/like/');
        self::assertSame(0, $this->repository->count([]));
    }
}
