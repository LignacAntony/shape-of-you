<?php

namespace App\Controller;

use App\Entity\Wardrobe;
use App\Form\WardrobeType;
use App\Repository\WardrobeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Entity\Outfit;
use App\Entity\OutfitItem;
use App\Entity\ClothingItem;
use App\Form\ClothingItemType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\CategoryItemRepository;

final class WardrobeController extends AbstractController
{
    #[Route('/admin/wardrobe', name: 'app_wardrobe_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(WardrobeRepository $wardrobeRepository): Response
    {
        return $this->render('admin/wardrobe/index.html.twig', [
            'wardrobes' => $wardrobeRepository->findAll(),
        ]);
    }

    #[Route('/admin/wardrobe/new', name: 'app_wardrobe_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $wardrobe = new Wardrobe();
        $form = $this->createForm(WardrobeType::class, $wardrobe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($wardrobe);
            $entityManager->flush();

            return $this->redirectToRoute('app_wardrobe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/wardrobe/new.html.twig', [
            'wardrobe' => $wardrobe,
            'form' => $form,
        ]);
    }

    #[Route('/admin/wardrobe/{id}', name: 'app_wardrobe_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Wardrobe $wardrobe): Response
    {
        return $this->render('admin/wardrobe/show.html.twig', [
            'wardrobe' => $wardrobe,
        ]);
    }

    #[Route('/admin/wardrobe/{id}/edit', name: 'app_wardrobe_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Wardrobe $wardrobe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WardrobeType::class, $wardrobe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_wardrobe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/wardrobe/edit.html.twig', [
            'wardrobe' => $wardrobe,
            'form' => $form,
        ]);
    }

    #[Route('/admin/wardrobe/{id}', name: 'app_wardrobe_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Wardrobe $wardrobe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $wardrobe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($wardrobe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_wardrobe_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/wardrobe', name: 'user_wardrobe', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function userWardrobe(WardrobeRepository $wardrobeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $wardrobes = $wardrobeRepository->findBy(['author' => $user]);
        $outfits = $user->getOutfits();

        return $this->render('wardrobe/index.html.twig', [
            'wardrobes' => $wardrobes,
            'outfits' => $outfits,
        ]);
    }

    #[Route('/wardrobe/{id}/details', name: 'wardrobe_details', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function wardrobeDetails(Wardrobe $wardrobe, CategoryItemRepository $categoryRepository): Response
    {
        if ($wardrobe->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette garde-robe.');
        }

        return $this->render('wardrobe/details.html.twig', [
            'wardrobe' => $wardrobe,
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/outfit/{id}/details', name: 'outfit_details', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function outfitDetails(Outfit $outfit): Response
    {
        if ($outfit->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette tenue.');
        }

        return $this->render('wardrobe/outfit_details.html.twig', [
            'outfit' => $outfit,
        ]);
    }

    #[Route('/clothing/{id}/details', name: 'clothing_details', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function clothingDetails(OutfitItem $outfitItem): Response
    {
        if ($outfitItem->getOutfit()->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce vêtement.');
        }

        return $this->render('wardrobe/clothing_details.html.twig', [
            'outfitItem' => $outfitItem,
        ]);
    }

    #[Route('/wardrobe/clothing/add', name: 'wardrobe_clothing_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addClothing(Request $request, EntityManagerInterface $entityManager): Response
    {
        $clothingItem = new ClothingItem();
        $form = $this->createForm(ClothingItemType::class, $clothingItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $clothingItem->setCreatedAt(new \DateTimeImmutable());
            
            // Gestion des images
            /** @var UploadedFile[] $images */
            $images = $form->get('images')->getData();
            if ($images) {
                $uploadDir = $this->getParameter('upload_directory');
                foreach ($images as $image) {
                    $newFilename = uniqid().'.'.$image->guessExtension();
                    $image->move($uploadDir, $newFilename);
                    $clothingItem->addImage($newFilename);
                }
            }

            // Création de l'OutfitItem pour lier le vêtement à la garde-robe
            $wardrobeId = $request->request->get('wardrobe_id');
            $wardrobe = $entityManager->getRepository(Wardrobe::class)->find($wardrobeId);
            
            if (!$wardrobe) {
                throw $this->createNotFoundException('Garde-robe non trouvée');
            }

            if ($wardrobe->getAuthor() !== $this->getUser()) {
                throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette garde-robe.');
            }

            $outfitItem = new OutfitItem();
            $outfitItem->setClothingItem($clothingItem);
            $outfitItem->setWardrobe($wardrobe);

            $entityManager->persist($clothingItem);
            $entityManager->persist($outfitItem);
            $entityManager->flush();

            $this->addFlash('success', 'Le vêtement a été ajouté avec succès.');
            return $this->redirectToRoute('wardrobe_details', [
                'id' => $wardrobeId
            ]);
        }

        return $this->json([
            'status' => 'error',
            'message' => 'Le formulaire contient des erreurs.',
            'errors' => $form->getErrors(true)
        ], Response::HTTP_BAD_REQUEST);
    }
}
