<?php

namespace App\Controller;

use App\Entity\OutfitItem;
use App\Form\OutfitItemType;
use App\Repository\OutfitItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/outfit/item')]
#[IsGranted('ROLE_ADMIN')]
final class OutfitItemController extends AbstractController
{
    #[Route(name: 'app_outfit_item_index', methods: ['GET'])]
    public function index(OutfitItemRepository $outfitItemRepository): Response
    {
        return $this->render('outfit_item/index.html.twig', [
            'outfit_items' => $outfitItemRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_outfit_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $outfitItem = new OutfitItem();
        $form = $this->createForm(OutfitItemType::class, $outfitItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($outfitItem);
            $entityManager->flush();

            return $this->redirectToRoute('app_outfit_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('outfit_item/new.html.twig', [
            'outfit_item' => $outfitItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_outfit_item_show', methods: ['GET'])]
    public function show(OutfitItem $outfitItem): Response
    {
        return $this->render('outfit_item/show.html.twig', [
            'outfit_item' => $outfitItem,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_outfit_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OutfitItem $outfitItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OutfitItemType::class, $outfitItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_outfit_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('outfit_item/edit.html.twig', [
            'outfit_item' => $outfitItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_outfit_item_delete', methods: ['POST'])]
    public function delete(Request $request, OutfitItem $outfitItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$outfitItem->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($outfitItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_outfit_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
