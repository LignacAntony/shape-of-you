<?php

namespace App\Tests\Controller;

use App\Entity\CategoryItem;
use App\Entity\User;
use App\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CategoryItemControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/admin/category/item/';
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(CategoryItem::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Clean up users first
        $userRepository = $this->manager->getRepository(User::class);
        foreach ($userRepository->findAll() as $user) {
            $this->manager->remove($user);
        }
        $this->manager->flush();

        // Create and login as admin user
        $user = new User();
        $user->setEmail('admin@test.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'test123'));
        
        // Create profile for admin user
        $profile = new Profile();
        $profile->setAppUser($user);
        $user->setProfile($profile);
        
        $this->manager->persist($user);
        $this->manager->persist($profile);
        $this->manager->flush();

        $this->client->loginUser($user);

        // Clean up database
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
        self::assertPageTitleContains('CategoryItem index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Create', [
            'category_item[name]' => 'Testing',
            'category_item[description]' => 'Testing'
        ]);

        self::assertResponseRedirects('/admin/category/item');

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $fixture = new CategoryItem();
        $fixture->setName('Vêtements');
        $fixture->setDescription('Catégorie des vêtements');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('CategoryItem');
    }

    public function testEdit(): void
    {
        $fixture = new CategoryItem();
        $fixture->setName('Vêtements');
        $fixture->setDescription('Catégorie des vêtements');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'category_item[name]' => 'Something New',
            'category_item[description]' => 'Something New'
        ]);

        self::assertResponseRedirects('/admin/category/item');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getDescription());
    }

    public function testRemove(): void
    {
        $fixture = new CategoryItem();
        $fixture->setName('Vêtements');
        $fixture->setDescription('Catégorie des vêtements');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/admin/category/item');
        self::assertSame(0, $this->repository->count([]));
    }
}
