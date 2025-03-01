<?php

namespace App\Controller;

use App\Entity\Outfit;
use App\Entity\OutfitItem;
use App\Entity\ClothingItem;
use App\Entity\CategoryItem;
use App\Entity\Wardrobe;
use App\Entity\Like;
use App\Entity\Review;
use App\Entity\User;
use App\Form\OutfitAdminType;
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
use Symfony\Component\Security\Core\User\UserInterface;

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
        $form = $this->createForm(OutfitAdminType::class, $outfit);
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
        $form = $this->createForm(OutfitAdminType::class, $outfit);
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
        $outfit = $this->outfitRepository->findOutfitWithPublicAccess($id, $this->getUser());

        if (!$outfit) {
            throw $this->createNotFoundException('Tenue non trouvée');
        }

        // Seul l'auteur peut voir le formulaire d'ajout de vêtements
        $form = null;
        if ($outfit->getAuthor() === $this->getUser()) {
            $clothingItem = new ClothingItem();
            $form = $this->createForm(ClothingItemType::class, $clothingItem, [
                'user' => $this->getUser(),
                'wardrobe' => $outfit->getOutfitItems()->first() ? $outfit->getOutfitItems()->first()->getWardrobe() : null,
                'default_outfit' => $outfit
            ]);
        }

        return $this->render('outfit/details.html.twig', [
            'outfit' => $outfit,
            'outfitItems' => $this->outfitItemRepository->findOutfitItemsByOutfit($outfit),
            'allOutfitItems' => $outfit->getAuthor() === $this->getUser() ? $this->outfitItemRepository->findOutfitItemsByUser($this->getUser()) : [],
            'categories' => $this->entityManager->getRepository(CategoryItem::class)->findAll(),
            'form' => $form,
            'canEdit' => $outfit->getAuthor() === $this->getUser()
        ]);
    }

    #[Route('/outfit/{outfitId}/remove-item/{outfitItemId}', name: 'outfit_remove_item', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function removeItem(Request $request, int $outfitId, int $outfitItemId): JsonResponse
    {
        if (!$this->isCsrfTokenValid('remove_item' . $outfitItemId, $request->toArray()['_token'] ?? '')) {
            return $this->json(['status' => 'error', 'message' => 'Token CSRF invalide'], Response::HTTP_BAD_REQUEST);
        }

        $outfit = $this->outfitRepository->find($outfitId);
        if (!$outfit || $outfit->getAuthor() !== $this->getUser()) {
            return $this->json(['status' => 'error', 'message' => 'Tenue non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $outfitItem = $this->outfitItemRepository->find($outfitItemId);
        if (!$outfitItem || !$outfitItem->getOutfits()->contains($outfit)) {
            return $this->json(['status' => 'error', 'message' => 'Item non trouvé'], Response::HTTP_NOT_FOUND);
        }

        try {
            $outfitItem->removeOutfit($outfit);
            $this->entityManager->flush();
            return $this->json(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->json(['status' => 'error', 'message' => 'Erreur lors de la suppression'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/outfit/{id}/add-existing-item', name: 'outfit_add_existing_item', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addExistingItem(Request $request, int $id): JsonResponse
    {
        $outfit = $this->outfitRepository->findOutfitWithAccessCheck($id, $this->getUser());

        if (!$outfit) {
            return $this->json([
                'status' => 'error',
                'message' => 'Tenue non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $outfitItemId = $data['outfitItemId'] ?? null;

        if (!$outfitItemId) {
            return $this->json([
                'status' => 'error',
                'message' => 'ID du vêtement manquant'
            ], Response::HTTP_BAD_REQUEST);
        }

        $outfitItem = $this->outfitItemRepository->find($outfitItemId);
        if (!$outfitItem || $outfitItem->getWardrobe()->getAuthor() !== $this->getUser()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Vêtement non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $outfitItem->addOutfit($outfit);
            $this->entityManager->flush();

            return $this->json([
                'status' => 'success',
                'message' => 'Vêtement ajouté à la tenue avec succès'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de l\'ajout du vêtement'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
            $outfit->setWardrobe($wardrobe);

            // Debug de la valeur de isPublished
            $formData = $request->request->all();
            $isPublished = $formData['outfit']['isPublished'] ?? null;
            $this->addFlash('debug', sprintf(
                'Form isPublished: %s, Entity isPublished: %s',
                var_export($isPublished, true),
                var_export($outfit->getIsPublished(), true)
            ));

            // Forcer la valeur de isPublished depuis le formulaire
            $outfit->setIsPublished($form->get('isPublished')->getData());

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

    #[Route('/outfit/{id}/edit-user', name: 'outfit_edit_user', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editOutfitUser(Request $request, int $id): Response
    {
        $outfit = $this->outfitRepository->findOutfitWithAccessCheck($id, $this->getUser());
        if (!$outfit) {
            throw $this->createNotFoundException('Tenue non trouvée');
        }

        if ($request->isXmlHttpRequest() && $request->query->get('action') === 'delete_image') {
            $data = json_decode($request->getContent(), true);
            $imagePath = $data['imagePath'] ?? null;

            if (!$imagePath) {
                return new JsonResponse(['error' => 'Aucun chemin d\'image fourni'], 400);
            }

            $uploadDir = $this->getParameter('upload_directory');
            $fullImagePath = $uploadDir . '/' . basename($imagePath);
            if (file_exists($fullImagePath)) {
                unlink($fullImagePath);
            }

            $outfit->removeImage($imagePath);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true]);
        }

        $form = $this->createForm(OutfitType::class, $outfit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $outfit->setUpdateDateAt(new \DateTime());

            /** @var UploadedFile[] $images */
            $images = $form->get('images')->getData();
            if ($images) {
                $uploadDir = $this->getParameter('upload_directory');
                foreach ($images as $image) {
                    $newFilename = uniqid() . '.' . $image->guessExtension();
                    $image->move($uploadDir, $newFilename);
                    $outfit->addImage('uploads/images/' . $newFilename);
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'La tenue a été modifiée avec succès.');
            return $this->redirectToRoute('outfit_details', ['id' => $outfit->getId()]);
        }

        return $this->render('outfit/edit.html.twig', [
            'outfit' => $outfit,
            'form'   => $form,
        ]);
    }


    #[Route('/outfit/{id}/delete-user', name: 'outfit_delete_user', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteOutfitUser(Request $request, int $id): JsonResponse
    {
        $outfit = $this->outfitRepository->findOutfitWithAccessCheck($id, $this->getUser());

        if (!$outfit) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Tenue non trouvée'
            ], 404);
        }

        try {
            // Supprimer les images physiques si nécessaire
            foreach ($outfit->getImages() as $imagePath) {
                $fullPath = $this->getParameter('kernel.project_dir') . '/public/' . $imagePath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

            $this->entityManager->remove($outfit);
            $this->entityManager->flush();

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Tenue supprimée avec succès',
                'redirect' => $this->generateUrl('user_wardrobe')
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la suppression de la tenue'
            ], 500);
        }
    }

    #[Route('/outfit/{id}/toggle-like', name: 'outfit_toggle_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleLike(int $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $outfit = $this->outfitRepository->findOutfitWithPublicAccess($id, $user);

        if (!$outfit) {
            return $this->json([
                'status' => 'error',
                'message' => 'Tenue non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }

        $existingLike = $this->entityManager->getRepository(Like::class)->findOneBy([
            'author' => $user,
            'outfit' => $outfit
        ]);

        if ($existingLike) {
            // Unlike
            $this->entityManager->remove($existingLike);
            $isLiked = false;
        } else {
            // Like
            $like = new Like();
            $like->setAuthor($user);
            $like->setOutfit($outfit);
            $like->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($like);
            $isLiked = true;
        }

        $this->entityManager->flush();

        return $this->json([
            'status' => 'success',
            'likesCount' => $outfit->getLikesCount(),
            'isLiked' => $isLiked
        ]);
    }

    #[Route('/outfit/{id}/is-liked', name: 'outfit_is_liked', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function isLiked(int $id): JsonResponse
    {
        $outfit = $this->outfitRepository->findOutfitWithPublicAccess($id, $this->getUser());

        if (!$outfit) {
            return $this->json([
                'status' => 'error',
                'message' => 'Tenue non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }

        $isLiked = $outfit->getLikes()->exists(function ($key, $like) {
            return $like->getAuthor() === $this->getUser();
        });

        return $this->json([
            'status' => 'success',
            'isLiked' => $isLiked
        ]);
    }

    #[Route('/outfit/{id}/review', name: 'outfit_add_review', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addReview(Request $request, Outfit $outfit, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $content = json_decode($request->getContent(), true)['content'] ?? null;

        if (!$content) {
            return $this->json([
                'status' => 'error',
                'message' => 'Le contenu du commentaire est requis'
            ], Response::HTTP_BAD_REQUEST);
        }

        $review = new Review();
        $review->setContent($content);
        $review->setAuthor($user);
        $review->setOutfit($outfit);
        $review->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($review);
        $entityManager->flush();

        return $this->json([
            'status' => 'success',
            'review' => [
                'avatar' => '/uploads/avatars/' . $review->getAuthor()->getProfile()->getAvatar(),
                'content' => $review->getContent(),
                'author' => $review->getAuthor()->getUsername(),
                'createdAt' => $review->getCreatedAt()->format('d/m/Y H:i')
            ]
        ]);
    }
}
