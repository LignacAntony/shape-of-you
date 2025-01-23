<?php

namespace App\Controller;

use App\Entity\ClothingItem;
use App\Form\ClothingItemType;
use App\Repository\ClothingItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/clothing/item')]
#[IsGranted('ROLE_ADMIN')]
final class ClothingItemController extends AbstractController
{
    #[Route(name: 'app_clothing_item_index', methods: ['GET'])]
    public function index(ClothingItemRepository $clothingItemRepository): Response
    {
        return $this->render('clothing_item/index.html.twig', [
            'clothing_items' => $clothingItemRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_clothing_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $clothingItem = new ClothingItem();
        $form = $this->createForm(ClothingItemType::class, $clothingItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($clothingItem);
            $entityManager->flush();

            return $this->redirectToRoute('app_clothing_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('clothing_item/new.html.twig', [
            'clothing_item' => $clothingItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_clothing_item_show', methods: ['GET'])]
    public function show(ClothingItem $clothingItem): Response
    {
        return $this->render('clothing_item/show.html.twig', [
            'clothing_item' => $clothingItem,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_clothing_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ClothingItem $clothingItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClothingItemType::class, $clothingItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_clothing_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('clothing_item/edit.html.twig', [
            'clothing_item' => $clothingItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_clothing_item_delete', methods: ['POST'])]
    public function delete(Request $request, ClothingItem $clothingItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $clothingItem->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($clothingItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_clothing_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
