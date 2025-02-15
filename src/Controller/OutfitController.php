<?php

namespace App\Controller;

use App\Entity\Outfit;
use App\Entity\OutfitItem;
use App\Entity\ClothingItem;
use App\Entity\CategoryItem;
use App\Entity\Wardrobe;
use App\Form\OutfitType;
use App\Form\ClothingItemType;
use App\Repository\OutfitRepository;
use App\Repository\OutfitItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class OutfitController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OutfitRepository $outfitRepository,
        private OutfitItemRepository $outfitItemRepository
    ) {
    }

    #[Route('/admin/outfit', name: 'app_outfit_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(OutfitRepository $outfitRepository): Response
    {
        return $this->render('admin/outfit/index.html.twig', [
            'outfits' => $outfitRepository->findAll(),
        ]);
    }

    #[Route('/admin/outfit/new', name: 'app_outfit_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $outfit = new Outfit();
        $form = $this->createForm(OutfitType::class, $outfit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($outfit);
            $entityManager->flush();

            return $this->redirectToRoute('app_outfit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/outfit/new.html.twig', [
            'outfit' => $outfit,
            'form' => $form,
        ]);
    }

    #[Route('/admin/outfit/{id}', name: 'app_outfit_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Outfit $outfit): Response
    {
        return $this->render('admin/outfit/show.html.twig', [
            'outfit' => $outfit,
        ]);
    }

    #[Route('/admin/outfit/{id}/edit', name: 'app_outfit_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Outfit $outfit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OutfitType::class, $outfit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_outfit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/outfit/edit.html.twig', [
            'outfit' => $outfit,
            'form' => $form,
        ]);
    }

    #[Route('/admin/outfit/{id}', name: 'app_outfit_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Outfit $outfit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $outfit->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($outfit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_outfit_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/outfit/{id}/details', name: 'outfit_details')]
    #[IsGranted('ROLE_USER')]
    public function details(int $id): Response
    {
        $outfit = $this->outfitRepository->findOutfitWithAccessCheck($id, $this->getUser());
        
        if (!$outfit) {
            throw $this->createNotFoundException('Tenue non trouvée');
        }

        $clothingItem = new ClothingItem();
        $form = $this->createForm(ClothingItemType::class, $clothingItem, [
            'user' => $this->getUser(),
            'wardrobe' => $outfit->getOutfitItems()->first() ? $outfit->getOutfitItems()->first()->getWardrobe() : null,
            'default_outfit' => $outfit
        ]);

        return $this->render('outfit/details.html.twig', [
            'outfit' => $outfit,
            'outfitItems' => $this->outfitItemRepository->findOutfitItemsByOutfit($outfit),
            'categories' => $this->entityManager->getRepository(CategoryItem::class)->findAll(),
            'form' => $form
        ]);
    }

    #[Route('/outfit/{outfitId}/remove-item/{outfitItemId}', name: 'outfit_remove_item', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function removeItem(Request $request, int $outfitId, int $outfitItemId): JsonResponse
    {
        if (!$this->isCsrfTokenValid('remove_item'.$outfitItemId, $request->toArray()['_token'] ?? '')) {
            return $this->json(['status' => 'error', 'message' => 'Token CSRF invalide'], Response::HTTP_BAD_REQUEST);
        }

        $outfit = $this->outfitRepository->find($outfitId);
        if (!$outfit || $outfit->getAuthor() !== $this->getUser()) {
            return $this->json(['status' => 'error', 'message' => 'Tenue non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $outfitItem = $this->outfitItemRepository->find($outfitItemId);
        if (!$outfitItem) {
            return $this->json(['status' => 'error', 'message' => 'Item non trouvé'], Response::HTTP_NOT_FOUND);
        }

        try {
            $outfit->removeOutfitItem($outfitItem);
            $this->entityManager->flush();
            return $this->json(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->json(['status' => 'error', 'message' => 'Erreur lors de la suppression'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/outfit/{id}/add-existing-item', name: 'outfit_add_existing_item', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function addExistingItem(Request $request, int $id): Response
    {
        $outfit = $this->outfitRepository->findOutfitWithAccessCheck($id, $this->getUser());
        
        if (!$outfit) {
            throw $this->createNotFoundException('Tenue non trouvée');
        }

        if ($request->isMethod('POST')) {
            $clothingItemIds = $request->request->all('clothing_items');
            
            foreach ($clothingItemIds as $clothingItemId) {
                $clothingItem = $this->entityManager->getRepository(ClothingItem::class)->find($clothingItemId);
                
                if ($clothingItem && $clothingItem->getWardrobe()->getOwner() === $this->getUser()) {
                    $outfitItem = new OutfitItem();
                    $outfit->addOutfitItem($outfitItem);
                    $outfitItem->setClothingItem($clothingItem);
                    $outfitItem->setWardrobe($clothingItem->getWardrobe());
                    $outfitItem->setSize($clothingItem->getSize());
                    
                    $this->entityManager->persist($outfitItem);
                }
            }
            
            $this->entityManager->flush();
            
            return $this->redirectToRoute('outfit_details', ['id' => $outfit->getId()]);
        }

        return $this->render('outfit/add_existing_item.html.twig', [
            'outfit' => $outfit
        ]);
    }

    #[Route('/outfit/create/{wardrobeId}', name: 'outfit_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, int $wardrobeId): JsonResponse
    {
        $wardrobe = $this->entityManager->getRepository(Wardrobe::class)->find($wardrobeId);
        
        if (!$wardrobe || $wardrobe->getAuthor() !== $this->getUser()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Garde-robe non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }

        $outfit = new Outfit();
        $form = $this->createForm(OutfitType::class, $outfit);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Le formulaire n\'a pas été soumis correctement.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = [
                    'field' => $error->getOrigin()->getName(),
                    'message' => $error->getMessage()
                ];
            }

            return $this->json([
                'status' => 'error',
                'message' => 'Le formulaire contient des erreurs.',
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $outfit->setAuthor($this->getUser());
            $outfit->setCreatedAt(new \DateTimeImmutable());
            $outfit->setLikesCount(0);
            $outfit->setIsPublished(false);

            // Gestion des images
            /** @var UploadedFile[] $imageFiles */
            $imageFiles = $form->get('images')->getData();
            
            if ($imageFiles) {
                $uploadDir = $this->getParameter('upload_directory');
                
                foreach ($imageFiles as $imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                    
                    try {
                        $imageFile->move($uploadDir, $newFilename);
                        $outfit->addImage('uploads/images/' . $newFilename);
                    } catch (\Exception $e) {
                        return $this->json([
                            'status' => 'error',
                            'message' => 'Une erreur est survenue lors de l\'upload des images.'
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
            }
            
            $this->entityManager->persist($outfit);
            $this->entityManager->flush();

            return $this->json([
                'status' => 'success',
                'message' => 'La tenue a été créée avec succès.',
                'redirect' => $this->generateUrl('outfit_details', ['id' => $outfit->getId()])
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la création de la tenue.',
                'error_details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
