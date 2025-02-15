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
use App\Form\OutfitItemType;
use App\Form\OutfitType;

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

        $clothingItem = new ClothingItem();
        $form = $this->createForm(ClothingItemType::class, $clothingItem, [
            'user' => $this->getUser(),
            'wardrobe' => $wardrobe
        ]);

        $outfit = new Outfit();
        $outfitForm = $this->createForm(OutfitType::class, $outfit);

        return $this->render('wardrobe/details.html.twig', [
            'wardrobe' => $wardrobe,
            'categories' => $categoryRepository->findAll(),
            'form' => $form,
            'outfit_form' => $outfitForm
        ]);
    }

    #[Route('/clothing/{id}/details', name: 'clothing_details', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function clothingDetails(OutfitItem $outfitItem): Response
    {
        if ($outfitItem->getWardrobe()->getAuthor() !== $this->getUser()) {
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
        $wardrobeId = $request->request->get('wardrobe_id');
        if (!$wardrobeId) {
            throw new \InvalidArgumentException('L\'ID de la garde-robe est manquant.');
        }

        $wardrobe = $entityManager->getRepository(Wardrobe::class)->find($wardrobeId);
        if (!$wardrobe) {
            throw $this->createNotFoundException('Garde-robe non trouvée');
        }

        if ($wardrobe->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette garde-robe.');
        }

        $outfitId = $request->request->get('outfit');
        $outfit = null;
        if ($outfitId) {
            $outfit = $entityManager->getRepository(Outfit::class)->find($outfitId);
            if (!$outfit || $outfit->getAuthor() !== $this->getUser()) {
                throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette tenue.');
            }
        }

        $clothingItem = new ClothingItem();
        $form = $this->createForm(ClothingItemType::class, $clothingItem, [
            'user' => $this->getUser(),
            'wardrobe' => $wardrobe,
            'default_outfit' => $outfit
        ]);
        
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Le formulaire n\'a pas été soumis correctement.',
                'debug' => [
                    'request_method' => $request->getMethod(),
                    'content_type' => $request->headers->get('Content-Type'),
                    'post_data' => $request->request->all(),
                ]
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

            $fieldErrors = [];
            foreach ($form->all() as $field) {
                if ($field->getErrors()->count() > 0) {
                    $fieldErrors[$field->getName()] = [];
                    foreach ($field->getErrors() as $error) {
                        $fieldErrors[$field->getName()][] = $error->getMessage();
                    }
                }
            }

            return $this->json([
                'status' => 'error',
                'message' => 'Le formulaire contient des erreurs.',
                'errors' => $errors,
                'field_errors' => $fieldErrors,
                'form_data' => $request->request->all(),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $clothingItem->setCreatedAt(new \DateTimeImmutable());
            
            /** @var UploadedFile[] $images */
            $images = $form->get('images')->getData();
            if ($images) {
                $uploadDir = $this->getParameter('upload_directory');
                
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                foreach ($images as $image) {
                    $newFilename = uniqid().'.'.$image->guessExtension();
                    $image->move($uploadDir, $newFilename);
                    $clothingItem->addImage('uploads/images/'.$newFilename);
                }
            }

            $outfitItem = new OutfitItem();
            $outfitItem->setClothingItem($clothingItem);
            $outfitItem->setWardrobe($wardrobe);
            $outfitItem->setSize($form->get('size')->getData());

            $selectedOutfit = $form->get('outfit')->getData();
            if ($selectedOutfit) {
                $selectedOutfit->addOutfitItem($outfitItem);
                $entityManager->persist($selectedOutfit);
            }

            $entityManager->persist($clothingItem);
            $entityManager->persist($outfitItem);
            $entityManager->flush();

            return $this->json([
                'status' => 'success',
                'message' => 'Le vêtement a été ajouté avec succès.',
                'redirect' => $selectedOutfit ? $this->generateUrl('outfit_details', ['id' => $selectedOutfit->getId()]) : $this->generateUrl('wardrobe_details', ['id' => $wardrobe->getId()])
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de l\'ajout du vêtement.',
                'error_details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/clothing/{id}/edit', name: 'clothing_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editClothing(Request $request, OutfitItem $outfitItem, EntityManagerInterface $entityManager): Response
    {
        if ($outfitItem->getWardrobe()->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce vêtement.');
        }

        $clothingItem = $outfitItem->getClothingItem();
        $clothingForm = $this->createForm(ClothingItemType::class, $clothingItem, [
            'user' => $this->getUser(),
            'wardrobe' => $outfitItem->getWardrobe()
        ]);
        
        $outfitForm = $this->createForm(OutfitItemType::class, $outfitItem, [
            'user' => $this->getUser()
        ]);
        
        $outfitForm->handleRequest($request);
        $clothingForm->handleRequest($request);

        if ($outfitForm->isSubmitted() && $outfitForm->isValid()) {
            try {
                $entityManager->persist($outfitItem);
                $entityManager->flush();
                $this->addFlash('success', 'Les tenues ont été mises à jour avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour des tenues.');
            }
            return $this->redirectToRoute('clothing_details', ['id' => $outfitItem->getId()]);
        }

        if ($clothingForm->isSubmitted() && $clothingForm->isValid()) {
            try {
                /** @var UploadedFile[] $images */
                $images = $clothingForm->get('images')->getData();
                if ($images) {
                    $uploadDir = $this->getParameter('upload_directory');
                    
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    foreach ($images as $image) {
                        if ($image instanceof UploadedFile) {
                            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                            $newFilename = $originalFilename . '-' . uniqid() . '.' . $image->guessExtension();
                            
                            try {
                                $image->move($uploadDir, $newFilename);
                                $clothingItem->addImage('uploads/images/' . $newFilename);
                            } catch (\Exception $e) {
                                $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de l\'image ' . $originalFilename);
                                continue;
                            }
                        }
                    }
                }

                $outfitItem->setSize($clothingForm->get('size')->getData());
                
                $entityManager->persist($clothingItem);
                $entityManager->flush();

                $this->addFlash('success', 'Le vêtement a été modifié avec succès.');
                return $this->redirectToRoute('clothing_details', ['id' => $outfitItem->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification du vêtement : ' . $e->getMessage());
            }
        }

        return $this->render('wardrobe/edit_clothing.html.twig', [
            'outfitItem' => $outfitItem,
            'clothing_form' => $clothingForm,
            'outfit_form' => $outfitForm
        ]);
    }
}
