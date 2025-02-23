<?php

namespace App\Tests\Controller;

use App\Entity\Outfit;
use App\Entity\User;
use App\Entity\Profile;
use App\Entity\Wardrobe;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class OutfitControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;
    private string $path = '/admin/outfit';
    private UserPasswordHasherInterface $passwordHasher;
    private User $user;
    private Wardrobe $wardrobe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->repository = $this->entityManager->getRepository(Outfit::class);

        // Nettoyer la base de données
        $this->entityManager->createQuery('DELETE FROM App\Entity\Outfit')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\OutfitItem')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Wardrobe')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Profile')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();

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
        $this->entityManager->persist($this->user);
        $this->entityManager->persist($profile);
        $this->entityManager->persist($this->wardrobe);
        $this->entityManager->flush();

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
            'outfit[name]' => 'Test Outfit',
            'outfit[description]' => 'Test Description',
            'outfit[isPublished]' => true
        ]);

        $this->assertResponseRedirects('/admin/outfit');

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
            'outfit[name]' => '', // Nom vide pour déclencher une erreur de validation
            'outfit[description]' => 'Test Description',
            'outfit[isPublished]' => true
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
        $this->entityManager->persist($outfit);
        $this->entityManager->flush();

        $this->client->request('GET', sprintf('/admin/outfit/%s', $outfit->getId()));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('h1');
    }

    public function testEdit(): void
    {
        $outfit = $this->createOutfit();
        $this->entityManager->persist($outfit);
        $this->entityManager->flush();

        $this->client->request('GET', sprintf('%s/%s/edit', $this->path, $outfit->getId()));
        $this->assertResponseStatusCodeSame(200);

        $this->client->submitForm('Update', [
            'outfit[name]' => 'Updated Outfit',
            'outfit[description]' => 'Updated Description',
            'outfit[isPublished]' => false
        ]);

        $this->assertResponseRedirects('/admin/outfit');

        $updatedOutfit = $this->repository->find($outfit->getId());
        $this->assertNotNull($updatedOutfit);
        $this->assertEquals('Updated Outfit', $updatedOutfit->getName());
        $this->assertEquals('Updated Description', $updatedOutfit->getDescription());
        $this->assertFalse($updatedOutfit->getIsPublished());
    }

    public function testEditWithInvalidData(): void
    {
        $outfit = $this->createOutfit();
        $this->entityManager->persist($outfit);
        $this->entityManager->flush();

        $this->client->request('GET', sprintf('%s/%s/edit', $this->path, $outfit->getId()));
        $this->assertResponseStatusCodeSame(200);

        $crawler = $this->client->submitForm('Update', [
            'outfit[name]' => '',
            'outfit[description]' => 'Updated Description',
            'outfit[isPublished]' => false
        ]);

        $this->assertResponseStatusCodeSame(422);
        
        // Debug: afficher le contenu de la réponse
        var_dump($this->client->getResponse()->getContent());
        
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
        $this->entityManager->persist($outfit);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', sprintf('%s/%s', $this->path, $outfit->getId()));
        $this->client->submitForm('Delete');

        $this->assertResponseRedirects('/admin/outfit');
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
