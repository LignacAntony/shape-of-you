<?php

namespace App\Tests\Controller;

use App\Entity\CategoryItem;
use App\Entity\ClothingItem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ClothingItemControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ObjectManager $manager;
    private EntityRepository $repository;
    private CategoryItem $categoryItem;
    private string $path = '/clothing/item/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(ClothingItem::class);

        $this->categoryItem = new CategoryItem();
        $this->categoryItem->setName('Vêtements');
        $this->categoryItem->setDescription('Catégorie des vêtements');
        $this->manager->persist($this->categoryItem);

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

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $category = new CategoryItem();
        $category->setName('Test Category');
        $category->setDescription('Test Description');

        $this->client->submitForm('Save', [
            'clothing_item[name]' => 'Testing',
            'clothing_item[description]' => 'Testing',
            'clothing_item[brand]' => 'Testing',
            'clothing_item[color]' => 'Testing',
            'clothing_item[price]' => 'Testing',
            'clothing_item[aiTags]' => 'Testing',
            'clothing_item[createdAt]' => 'Testing',
            'clothing_item[category]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();

        $fixture = new ClothingItem();
        $fixture->setCategory($this->categoryItem);
        $fixture->setName('Nike Air Max 90');
        $fixture->setDescription('Les meilleures baskets du monde !');
        $fixture->setPrice('120');
        $fixture->setColor('Blanc');
        $fixture->setBrand('Nike');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ClothingItem');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new ClothingItem();
        $fixture->setCategory($this->categoryItem);
        $fixture->setName('Nike Air Max 90');
        $fixture->setDescription('Les meilleures baskets du monde !');
        $fixture->setPrice('120');
        $fixture->setColor('Blanc');
        $fixture->setBrand('Nike');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'clothing_item[name]' => 'Something New',
            'clothing_item[description]' => 'Something New',
            'clothing_item[brand]' => 'Something New',
            'clothing_item[color]' => 'Something New',
            'clothing_item[price]' => 'Something New',
            'clothing_item[aiTags]' => 'Something New',
            'clothing_item[createdAt]' => 'Something New',
            'clothing_item[category]' => 'Something New',
        ]);

        self::assertResponseRedirects('/clothing/item/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getBrand());
        self::assertSame('Something New', $fixture[0]->getColor());
        self::assertSame('Something New', $fixture[0]->getPrice());
        self::assertSame('Something New', $fixture[0]->getAiTags());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getCategory());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new ClothingItem();
        $fixture->setCategory($this->categoryItem);
        $fixture->setName('Nike Air Max 90');
        $fixture->setDescription('Les meilleures baskets du monde !');
        $fixture->setPrice('120');
        $fixture->setColor('Blanc');
        $fixture->setBrand('Nike');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/clothing/item/');
        self::assertSame(0, $this->repository->count([]));
    }
}
