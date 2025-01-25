<?php

namespace App\Controller;

use App\Entity\CategoryItem;
use App\Form\CategoryItemType;
use App\Repository\CategoryItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/category/item')]
#[IsGranted('ROLE_ADMIN')]
final class CategoryItemController extends AbstractController
{
    #[Route(name: 'app_category_item_index', methods: ['GET'])]
    public function index(CategoryItemRepository $categoryItemRepository): Response
    {
        return $this->render('category_item/index.html.twig', [
            'category_items' => $categoryItemRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_category_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categoryItem = new CategoryItem();
        $form = $this->createForm(CategoryItemType::class, $categoryItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categoryItem);
            $entityManager->flush();

            return $this->redirectToRoute('app_category_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category_item/new.html.twig', [
            'category_item' => $categoryItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_item_show', methods: ['GET'])]
    public function show(CategoryItem $categoryItem): Response
    {
        return $this->render('category_item/show.html.twig', [
            'category_item' => $categoryItem,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CategoryItem $categoryItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryItemType::class, $categoryItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_category_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category_item/edit.html.twig', [
            'category_item' => $categoryItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_item_delete', methods: ['POST'])]
    public function delete(Request $request, CategoryItem $categoryItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $categoryItem->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categoryItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_category_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
