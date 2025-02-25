<?php

namespace App\Tests\Controller;

use App\Entity\CategoryItem;
use App\Entity\User;
use App\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;

final class CategoryItemControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ObjectManager $manager;
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

    /**
     * Test direct manipulation of CategoryItem entity
     */
    public function testCategoryItemCRUD(): void
    {
        // 1. Create a CategoryItem directly in the database
        $categoryItem = new CategoryItem();
        $categoryItem->setName('Test Category');
        $categoryItem->setDescription('Test Description');
        
        $this->manager->persist($categoryItem);
        $this->manager->flush();
        
        $itemId = $categoryItem->getId();
        
        // 2. Verify it was created
        self::assertNotNull($itemId);
        self::assertEquals(1, $this->repository->count([]));
        
        // 3. Update the item directly in database
        $categoryItem->setName('Updated Category');
        $categoryItem->setDescription('Updated Description');
        $this->manager->flush();
        
        // 4. Verify update worked
        $this->manager->clear();
        $updatedItem = $this->repository->find($itemId);
        self::assertEquals('Updated Category', $updatedItem->getName());
        self::assertEquals('Updated Description', $updatedItem->getDescription());
        
        // 5. Delete the item
        $this->manager->remove($updatedItem);
        $this->manager->flush();
        
        // 6. Verify it was deleted
        self::assertEquals(0, $this->repository->count([]));
    }

    /**
     * Test accessing admin pages
     */
    public function testAdminPages(): void 
    {
        // 1. Create a CategoryItem directly in the database
        $categoryItem = new CategoryItem();
        $categoryItem->setName('Test Category');
        $categoryItem->setDescription('Test Description');
        
        $this->manager->persist($categoryItem);
        $this->manager->flush();
        
        $itemId = $categoryItem->getId();

        // 2. Test index page access (follow redirect)
        $this->client->followRedirects(true);
        $this->client->request('GET', $this->path);
        self::assertTrue($this->client->getResponse()->isSuccessful());
        
        // 3. Test show page access
        $this->client->request('GET', $this->path . $itemId);
        self::assertTrue($this->client->getResponse()->isSuccessful());
        
        // 4. Test edit page access
        $this->client->request('GET', $this->path . $itemId . '/edit');
        self::assertTrue($this->client->getResponse()->isSuccessful());
    }
}
