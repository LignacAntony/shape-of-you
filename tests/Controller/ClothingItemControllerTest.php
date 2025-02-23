<?php

namespace App\Tests\Controller;

use App\Entity\CategoryItem;
use App\Entity\ClothingItem;
use App\Entity\User;
use App\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ClothingItemControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ObjectManager $manager;
    private EntityRepository $repository;
    private CategoryItem $categoryItem;
    private string $path = '/admin/clothing/item/';
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(ClothingItem::class);
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

        // Create a category for clothing items
        $this->categoryItem = new CategoryItem();
        $this->categoryItem->setName('Vêtements');
        $this->categoryItem->setDescription('Catégorie des vêtements');
        $this->manager->persist($this->categoryItem);

        // Clean up clothing items
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
        self::assertPageTitleContains('ClothingItem index');
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $category = new CategoryItem();
        $category->setName('Test Category');
        $category->setDescription('Test Description');

        $this->client->submitForm('Create', [
            'clothing_item[name]' => 'Nike Air Max 90',
            'clothing_item[description]' => 'Les meilleures baskets du monde !',
            'clothing_item[brand]' => 'Nike',
            'clothing_item[color]' => 'Blanc',
            'clothing_item[price]' => '120',
            'clothing_item[category]' => $this->categoryItem->getId(),
        ]);

        self::assertResponseRedirects('/admin/clothing/item');

        self::assertSame(1, $this->repository->count([]));

        $fixture = $this->repository->findAll();
        self::assertSame('12000', $fixture[0]->getPrice());
    }

    public function testShow(): void
    {
        $createdAt = new \DateTimeImmutable();
        
        $fixture = new ClothingItem();
        $fixture->setCategory($this->categoryItem);
        $fixture->setName('Nike Air Max 90');
        $fixture->setDescription('Les meilleures baskets du monde !');
        $fixture->setPrice('12000');
        $fixture->setColor('Blanc');
        $fixture->setBrand('Nike');
        $fixture->setCreatedAt($createdAt);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ClothingItem');
    }

    public function testEdit(): void
    {
        $createdAt = new \DateTimeImmutable();
        
        $fixture = new ClothingItem();
        $fixture->setCategory($this->categoryItem);
        $fixture->setName('Nike Air Max 90');
        $fixture->setDescription('Les meilleures baskets du monde !');
        $fixture->setPrice('12000');
        $fixture->setColor('Blanc');
        $fixture->setBrand('Nike');
        $fixture->setCreatedAt($createdAt);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'clothing_item[name]' => 'Adidas Superstar',
            'clothing_item[description]' => 'Une autre paire de baskets légendaire',
            'clothing_item[brand]' => 'Adidas',
            'clothing_item[color]' => 'Noir',
            'clothing_item[price]' => '100',
            'clothing_item[category]' => $this->categoryItem->getId(),
        ]);

        self::assertResponseRedirects('/admin/clothing/item');

        $fixture = $this->repository->findAll();

        self::assertSame('Adidas Superstar', $fixture[0]->getName());
        self::assertSame('Une autre paire de baskets légendaire', $fixture[0]->getDescription());
        self::assertSame('Adidas', $fixture[0]->getBrand());
        self::assertSame('Noir', $fixture[0]->getColor());
        self::assertSame('10000', $fixture[0]->getPrice());
        self::assertSame($this->categoryItem->getId(), $fixture[0]->getCategory()->getId());
    }

    public function testRemove(): void
    {
        $createdAt = new \DateTimeImmutable();
        
        $fixture = new ClothingItem();
        $fixture->setCategory($this->categoryItem);
        $fixture->setName('Nike Air Max 90');
        $fixture->setDescription('Les meilleures baskets du monde !');
        $fixture->setPrice('12000');
        $fixture->setColor('Blanc');
        $fixture->setBrand('Nike');
        $fixture->setCreatedAt($createdAt);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/admin/clothing/item');
        self::assertSame(0, $this->repository->count([]));
    }
}
