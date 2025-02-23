<?php

namespace App\Tests\Controller;

use App\Entity\CategoryItem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CategoryItemControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ObjectManager $manager;
    private EntityRepository $repository;
    private string $path = '/category/item/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(CategoryItem::class);

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
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'category_item[name]' => 'Testing',
            'category_item[desription]' => 'Testing',
            'category_item[categoryParent]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new CategoryItem();
        $fixture->setName('Vêtements');
        $fixture->setDescription('Catégorie des vêtements');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('CategoryItem');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();

        $fixture = new CategoryItem();
        $fixture->setName('Vêtements');
        $fixture->setDescription('Catégorie des vêtements');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'category_item[name]' => 'Something New',
            'category_item[desription]' => 'Something New',
            'category_item[categoryParent]' => 'Something New',
        ]);

        self::assertResponseRedirects('/category/item/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getCategoryParent());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $fixture = new CategoryItem();
        $fixture->setName('Vêtements');
        $fixture->setDescription('Catégorie des vêtements');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/category/item/');
        self::assertSame(0, $this->repository->count([]));
    }
}
