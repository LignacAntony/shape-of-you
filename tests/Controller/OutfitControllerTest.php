<?php

namespace App\Tests\Controller;

use App\Entity\Outfit;
use App\Entity\Profile;
use App\Entity\User;
use App\Entity\Wardrobe;
use App\Repository\OutfitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OutfitControllerTest extends WebTestCase
{
    private string $path = '/admin/outfit';

    private EntityManagerInterface $manager;

    private OutfitRepository $repository;

    private Client $client;

    private User $user;

    private Wardrobe $wardrobe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->manager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Outfit::class);

        // Nettoyer la base de données
        $this->manager->createQuery('DELETE FROM App\Entity\Outfit')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\OutfitItem')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Wardrobe')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\Profile')->execute();
        $this->manager->createQuery('DELETE FROM App\Entity\User')->execute();

        // Créer un utilisateur admin
        $this->user = new User();
        $this->user->setEmail('test@example.com')
            ->setUsername('testuser')
            ->setPassword('testpass')
            ->setRoles(['ROLE_ADMIN']);

        // Créer un profil avec un avatar par défaut
        $profile = new Profile();
        $profile->setAppUser($this->user);
        $this->user->setProfile($profile);

        // Créer une garde-robe pour l'utilisateur
        $this->wardrobe = new Wardrobe();
        $this->wardrobe->setAuthor($this->user)
            ->setName('Ma garde-robe')
            ->setDescription('Description de ma garde-robe')
            ->setCreatedAt(new \DateTimeImmutable());

        // Persister les entités
        $this->manager->persist($this->user);
        $this->manager->persist($profile);
        $this->manager->persist($this->wardrobe);
        $this->manager->flush();

        // Connecter l'utilisateur
        $this->client->loginUser($this->user);
    }

    public function testIndex(): void
    {
        $this->client->request('GET', $this->path);
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('h1');
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%s/new', $this->path));
        $this->assertResponseStatusCodeSame(200);

        $this->client->submitForm('Create', [
            'outfit_admin[name]' => 'Test Outfit',
            'outfit_admin[description]' => 'Test Description',
            'outfit_admin[isPublished]' => true
        ]);

        $outfit = $this->repository->findOneBy(['name' => 'Test Outfit']);
        $this->assertNotNull($outfit);
        $this->assertEquals('Test Outfit', $outfit->getName());
        $this->assertEquals('Test Description', $outfit->getDescription());
        $this->assertTrue($outfit->getIsPublished());
    }

    public function testNewWithInvalidData(): void
    {
        $this->client->request('GET', sprintf('%s/new', $this->path));
        $this->assertResponseStatusCodeSame(200);

        $this->client->submitForm('Create', [
            'outfit_admin[name]' => '', // Nom vide pour déclencher une erreur de validation
            'outfit_admin[description]' => 'Test Description',
            'outfit_admin[isPublished]' => true
        ]);

        $this->assertResponseStatusCodeSame(422); // Unprocessable Entity
        $this->assertSelectorExists('.text-red-600');
        $this->assertSelectorTextContains('.text-red-600', 'Le nom est obligatoire');
    }

    public function testShow(): void
    {
        // S'assurer que l'utilisateur est connecté avant de créer l'outfit
        $this->client->loginUser($this->user);

        $outfit = $this->createOutfit();
        $this->manager->persist($outfit);
        $this->manager->flush();

        $this->client->request('GET', sprintf('/admin/outfit/%s', $outfit->getId()));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('h1');
    }

    public function testEdit(): void
    {
        $outfit = $this->createOutfit();
        $this->manager->persist($outfit);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s/%s/edit', $this->path, $outfit->getId()));
        $this->assertResponseStatusCodeSame(200);

        $this->client->submitForm('Update', [
            'outfit_admin[name]' => 'Updated Outfit',
            'outfit_admin[description]' => 'Updated Description',
            'outfit_admin[isPublished]' => false
        ]);

        $updatedOutfit = $this->repository->find($outfit->getId());
        $this->assertNotNull($updatedOutfit);
        $this->assertEquals('Updated Outfit', $updatedOutfit->getName());
        $this->assertEquals('Updated Description', $updatedOutfit->getDescription());
        $this->assertFalse($updatedOutfit->getIsPublished());
    }

    public function testEditWithInvalidData(): void
    {
        $outfit = $this->createOutfit();
        $this->manager->persist($outfit);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s/%s/edit', $this->path, $outfit->getId()));
        $this->assertResponseStatusCodeSame(200);

        $this->client->submitForm('Update', [
            'outfit_admin[name]' => '',
            'outfit_admin[description]' => 'Updated Description',
            'outfit_admin[isPublished]' => false
        ]);

        $this->assertResponseStatusCodeSame(422);

        $this->assertSelectorExists('.text-red-600');
        $this->assertSelectorTextContains('.text-red-600', 'Le nom est obligatoire');

        // Vérifier que l'outfit n'a pas été modifié
        $unchangedOutfit = $this->repository->find($outfit->getId());
        $this->assertNotNull($unchangedOutfit);
        $this->assertNotSame('', $unchangedOutfit->getName());
    }

    public function testRemove(): void
    {
        $outfit = $this->createOutfit();
        $this->manager->persist($outfit);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s/%s', $this->path, $outfit->getId()));
        $this->client->submitForm('Delete');

        $this->assertEquals(0, $this->repository->count([]));
    }

    private function createOutfit(): Outfit
    {
        $outfit = new Outfit();
        $outfit->setName('Test Outfit');
        $outfit->setDescription('Test Description');
        $outfit->setIsPublished(true);
        $outfit->setAuthor($this->user);
        $outfit->setWardrobe($this->wardrobe);
        $outfit->setCreatedAt(new \DateTimeImmutable());
        $outfit->setLikesCount(0);
        $outfit->setUpdateDateAt(new \DateTime());

        return $outfit;
    }
}
