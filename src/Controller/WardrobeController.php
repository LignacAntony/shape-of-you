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

#[Route('/admin/wardrobe')]
#[IsGranted('ROLE_ADMIN')]
final class WardrobeController extends AbstractController
{
    #[Route(name: 'app_wardrobe_index', methods: ['GET'])]
    public function index(WardrobeRepository $wardrobeRepository): Response
    {
        return $this->render('wardrobe/index.html.twig', [
            'wardrobes' => $wardrobeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_wardrobe_new', methods: ['GET', 'POST'])]
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

        return $this->render('wardrobe/new.html.twig', [
            'wardrobe' => $wardrobe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wardrobe_show', methods: ['GET'])]
    public function show(Wardrobe $wardrobe): Response
    {
        return $this->render('wardrobe/show.html.twig', [
            'wardrobe' => $wardrobe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_wardrobe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Wardrobe $wardrobe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WardrobeType::class, $wardrobe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_wardrobe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wardrobe/edit.html.twig', [
            'wardrobe' => $wardrobe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wardrobe_delete', methods: ['POST'])]
    public function delete(Request $request, Wardrobe $wardrobe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $wardrobe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($wardrobe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_wardrobe_index', [], Response::HTTP_SEE_OTHER);
    }
}
