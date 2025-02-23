<?php

namespace App\Tests\Controller;

use App\Entity\OutfitItem;
use App\Entity\User;
use App\Entity\Profile;
use App\Entity\Wardrobe;
use App\Entity\ClothingItem;
use App\Entity\CategoryItem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class OutfitItemControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ObjectManager $manager;
    private EntityRepository $repository;
    private string $path = '/admin/outfit-item/';
    private User $user;
    private Wardrobe $wardrobe;
    private ClothingItem $clothingItem;
    private CategoryItem $category;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(OutfitItem::class);
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Clean up database
        foreach ($this->manager->getRepository(OutfitItem::class)->findAll() as $object) {
            $this->manager->remove($object);
        }
        foreach ($this->manager->getRepository(ClothingItem::class)->findAll() as $object) {
            $this->manager->remove($object);
        }
        foreach ($this->manager->getRepository(Wardrobe::class)->findAll() as $object) {
            $this->manager->remove($object);
        }
        foreach ($this->manager->getRepository(CategoryItem::class)->findAll() as $object) {
            $this->manager->remove($object);
        }
        foreach ($this->manager->getRepository(Profile::class)->findAll() as $object) {
            $this->manager->remove($object);
        }
        foreach ($this->manager->getRepository(User::class)->findAll() as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();

        // Create user
        $this->user = new User();
        $this->user->setEmail('test@example.com');
        $this->user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $this->user->setPassword($passwordHasher->hashPassword($this->user, 'test123'));

        // Create profile
        $profile = new Profile();
        $profile->setAppUser($this->user);
        $this->user->setProfile($profile);

        $this->manager->persist($this->user);
        $this->manager->persist($profile);

        // Create category
        $this->category = new CategoryItem();
        $this->category->setName('Test Category');
        $this->category->setDescription('Test Description');
        $this->manager->persist($this->category);

        // Create wardrobe
        $this->wardrobe = new Wardrobe();
        $this->wardrobe->setName('Test Wardrobe');
        $this->wardrobe->setDescription('Test Description');
        $this->wardrobe->setAuthor($this->user);
        $this->manager->persist($this->wardrobe);

        // Create clothing item
        $this->clothingItem = new ClothingItem();
        $this->clothingItem->setName('Test Clothing');
        $this->clothingItem->setCategory($this->category);
        $this->clothingItem->setColor('Black');
        $this->clothingItem->setCreatedAt(new \DateTimeImmutable('2025-02-23 09:39:03'));
        $this->manager->persist($this->clothingItem);

        $this->manager->flush();

        $this->client->loginUser($this->user);
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);
        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Liste des éléments de tenue');
    }

    public function testNew(): void
    {
        $crawler = $this->client->request('GET', sprintf('%snew', $this->path));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Créer', [
            'outfit_item[size]' => 'M',
            'outfit_item[purchaseAt]' => '2024-01-01',
            'outfit_item[clothingItem]' => $this->clothingItem->getId(),
            'outfit_item[wardrobe]' => $this->wardrobe->getId(),
        ]);

        self::assertResponseRedirects('/admin/outfit-item/');
        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $fixture = new OutfitItem();
        $fixture->setWardrobe($this->wardrobe);
        $fixture->setClothingItem($this->clothingItem);
        $fixture->setSize('M');
        $fixture->setPurchaseAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Détails de l\'élément de tenue');
    }

    public function testEdit(): void
    {
        $fixture = new OutfitItem();
        $fixture->setWardrobe($this->wardrobe);
        $fixture->setClothingItem($this->clothingItem);
        $fixture->setSize('M');
        $fixture->setPurchaseAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Mettre à jour', [
            'outfit_item[size]' => 'L',
            'outfit_item[purchaseAt]' => '2024-02-01',
            'outfit_item[clothingItem]' => $this->clothingItem->getId(),
            'outfit_item[wardrobe]' => $this->wardrobe->getId(),
        ]);

        self::assertResponseRedirects('/admin/outfit-item/');

        $fixture = $this->repository->findAll();
        self::assertSame('L', $fixture[0]->getSize());
    }

    public function testRemove(): void
    {
        $fixture = new OutfitItem();
        $fixture->setWardrobe($this->wardrobe);
        $fixture->setClothingItem($this->clothingItem);
        $fixture->setSize('M');
        $fixture->setPurchaseAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $crawler = $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Supprimer')->form();
        $this->client->submit($form);

        self::assertResponseRedirects('/admin/outfit-item/');
        self::assertSame(0, $this->repository->count([]));
    }
}
