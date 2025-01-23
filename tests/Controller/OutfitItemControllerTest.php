<?php

namespace App\Tests\Controller;

use App\Entity\CategoryItem;
use App\Entity\ClothingItem;
use App\Entity\Outfit;
use App\Entity\OutfitItem;
use App\Entity\User;
use App\Entity\Wardrobe;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class OutfitItemControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private User $user;
    private Outfit $outfit;
    private Wardrobe $wardrobe;
    private CategoryItem $categoryItem;
    private ClothingItem $clothingItem;
    private UserPasswordHasherInterface $passwordHasher;
    private string $path = '/outfit/item/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(OutfitItem::class);

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
        $this->outfit->setName('My Title');
        $this->outfit->setDescription('My description');
        $this->outfit->setIsPublished(true);
        $this->outfit->setAuthor($this->user);
        $this->manager->persist($this->outfit);

        $this->wardrobe = new Wardrobe();
        $this->wardrobe->setAuthor($this->user);
        $this->wardrobe->setName('Armoire de John Doe');
        $this->wardrobe->setDescription('Armoire de John Doe');
        $this->manager->persist($this->wardrobe);

        $this->categoryItem = new CategoryItem();
        $this->categoryItem->setName('Vêtements');
        $this->categoryItem->setDesription('Catégorie des vêtements');
        $this->manager->persist($this->categoryItem);

        $this->clothingItem = new ClothingItem();
        $this->clothingItem->setCategory($this->categoryItem);
        $this->clothingItem->setName('Nike Air Max 90');
        $this->clothingItem->setDescription('Les meilleures baskets du monde !');
        $this->clothingItem->setPrice('120');
        $this->clothingItem->setColor('Blanc');
        $this->clothingItem->setBrand('Nike');
        $this->manager->persist($this->clothingItem);

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
        self::assertPageTitleContains('OutfitItem index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'outfit_item[size]' => 'Testing',
            'outfit_item[purchaseAt]' => 'Testing',
            'outfit_item[outfit]' => 'Testing',
            'outfit_item[clothingItem]' => 'Testing',
            'outfit_item[wardrobe]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();

        $fixture = new OutfitItem();
        $fixture->setOutfit($this->outfit);
        $fixture->setClothingItem($this->clothingItem);
        $fixture->setWardrobe($this->wardrobe);
        $fixture->setSize('M');
        $fixture->setPurchaseAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('OutfitItem');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new OutfitItem();
        $fixture->setOutfit($this->outfit);
        $fixture->setClothingItem($this->clothingItem);
        $fixture->setWardrobe($this->wardrobe);
        $fixture->setSize('M');
        $fixture->setPurchaseAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'outfit_item[size]' => 'Something New',
            'outfit_item[purchaseAt]' => 'Something New',
            'outfit_item[outfit]' => 'Something New',
            'outfit_item[clothingItem]' => 'Something New',
            'outfit_item[wardrobe]' => 'Something New',
        ]);

        self::assertResponseRedirects('/outfit/item/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getSize());
        self::assertSame('Something New', $fixture[0]->getPurchaseAt());
        self::assertSame('Something New', $fixture[0]->getOutfit());
        self::assertSame('Something New', $fixture[0]->getClothingItem());
        self::assertSame('Something New', $fixture[0]->getWardrobe());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new OutfitItem();
        $fixture->setOutfit($this->outfit);
        $fixture->setClothingItem($this->clothingItem);
        $fixture->setWardrobe($this->wardrobe);
        $fixture->setSize('M');
        $fixture->setPurchaseAt(new \DateTimeImmutable());

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/outfit/item/');
        self::assertSame(0, $this->repository->count([]));
    }
}
