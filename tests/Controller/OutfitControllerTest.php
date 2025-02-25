<?php

namespace App\Tests\Controller;

use App\Entity\Outfit;
use App\Entity\Profile;
use App\Entity\User;
use App\Entity\Wardrobe;
use App\Repository\OutfitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class OutfitControllerTest extends WebTestCase
{
    private string $path = '/admin/outfit';
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private OutfitRepository $repository;
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

    /**
     * Test direct manipulation of Outfit entity
     */
    public function testOutfitCRUD(): void
    {
        // 1. Create an Outfit directly in the database
        $outfit = new Outfit();
        $outfit->setName('Test Outfit');
        $outfit->setDescription('Test Description');
        $outfit->setIsPublished(true);
        $outfit->setAuthor($this->user);
        $outfit->setWardrobe($this->wardrobe);
        $outfit->setCreatedAt(new \DateTimeImmutable());
        $outfit->setLikesCount(0);
        $outfit->setUpdateDateAt(new \DateTime());
        
        $this->manager->persist($outfit);
        $this->manager->flush();
        
        $outfitId = $outfit->getId();
        
        // 2. Verify it was created
        self::assertNotNull($outfitId);
        self::assertEquals(1, $this->repository->count([]));
        
        // 3. Update the outfit directly in database
        $outfit->setName('Updated Outfit');
        $outfit->setDescription('Updated Description');
        $outfit->setIsPublished(false);
        $this->manager->flush();
        
        // 4. Verify update worked
        $this->manager->clear();
        $updatedOutfit = $this->repository->find($outfitId);
        self::assertEquals('Updated Outfit', $updatedOutfit->getName());
        self::assertEquals('Updated Description', $updatedOutfit->getDescription());
        self::assertFalse($updatedOutfit->getIsPublished());
        
        // 5. Delete the outfit
        $this->manager->remove($updatedOutfit);
        $this->manager->flush();
        
        // 6. Verify it was deleted
        self::assertEquals(0, $this->repository->count([]));
    }

    /**
     * Test accessing admin pages
     */
    public function testAdminPages(): void 
    {
        // 1. Create an Outfit directly in the database
        $outfit = new Outfit();
        $outfit->setName('Test Outfit');
        $outfit->setDescription('Test Description');
        $outfit->setIsPublished(true);
        $outfit->setAuthor($this->user);
        $outfit->setWardrobe($this->wardrobe);
        $outfit->setCreatedAt(new \DateTimeImmutable());
        $outfit->setLikesCount(0);
        $outfit->setUpdateDateAt(new \DateTime());
        
        $this->manager->persist($outfit);
        $this->manager->flush();
        
        $outfitId = $outfit->getId();

        // 2. Test index page access
        $this->client->followRedirects(true);
        $crawler = $this->client->request('GET', $this->path);
        
        // Vérifier que la réponse est un succès
        $statusCode = $this->client->getResponse()->getStatusCode();
        self::assertTrue(
            $statusCode >= 200 && $statusCode < 300,
            sprintf('Page d\'index a retourné un code HTTP %d au lieu d\'un succès', $statusCode)
        );
        
        // 3. Test show page access
        $this->client->request('GET', $this->path . '/' . $outfitId);
        $statusCode = $this->client->getResponse()->getStatusCode();
        self::assertTrue(
            $statusCode >= 200 && $statusCode < 300,
            sprintf('Page de détail a retourné un code HTTP %d au lieu d\'un succès', $statusCode)
        );
        
        // 4. Test edit page access
        $this->client->request('GET', $this->path . '/' . $outfitId . '/edit');
        $statusCode = $this->client->getResponse()->getStatusCode();
        self::assertTrue(
            $statusCode >= 200 && $statusCode < 300,
            sprintf('Page d\'édition a retourné un code HTTP %d au lieu d\'un succès', $statusCode)
        );
    }
}
