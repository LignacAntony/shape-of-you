<?php

namespace App\Tests\Controller;

use App\Entity\Outfit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class OutfitControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ObjectManager $manager;
    private EntityRepository $repository;
    private User $user;
    private UserPasswordHasherInterface $passwordHasher;
    private string $path = '/admin/outfit/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Outfit::class);

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
        self::assertPageTitleContains('Outfit index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'outfit[name]' => 'Testing',
            'outfit[description]' => 'Testing',
            'outfit[CreatedAt]' => 'Testing',
            'outfit[UpdateDateAt]' => 'Testing',
            'outfit[isPublished]' => 'Testing',
            'outfit[likesCount]' => 'Testing',
            'outfit[author]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Outfit();
        $fixture->setName('My Title');
        $fixture->setDescription('My description');
        $fixture->setIsPublished(true);
        $fixture->setAuthor($this->user);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Outfit');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Outfit();
        $fixture->setName('My Title');
        $fixture->setDescription('My description');
        $fixture->setIsPublished(true);
        $fixture->setAuthor($this->user);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'outfit[name]' => 'Something New',
            'outfit[description]' => 'Something New',
            'outfit[CreatedAt]' => 'Something New',
            'outfit[UpdateDateAt]' => 'Something New',
            'outfit[isPublished]' => 'Something New',
            'outfit[likesCount]' => 'Something New',
            'outfit[author]' => 'Something New',
        ]);

        self::assertResponseRedirects('/admin/outfit/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getUpdateDateAt());
        self::assertSame('Something New', $fixture[0]->getIsPublished());
        self::assertSame('Something New', $fixture[0]->getLikesCount());
        self::assertSame('Something New', $fixture[0]->getAuthor());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Outfit();
        $fixture->setName('My Title');
        $fixture->setDescription('My description');
        $fixture->setIsPublished(true);
        $fixture->setAuthor($this->user);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/admin/outfit/');
        self::assertSame(0, $this->repository->count([]));
    }
}
