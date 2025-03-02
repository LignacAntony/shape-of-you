<?php

namespace App\Controller;

use App\Repository\CategoryItemRepository;
use App\Repository\OutfitRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route(name: 'app_admin_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, OutfitRepository $outfitRepository, CategoryItemRepository $categoryItemRepository): Response
    {
        $userCount = $userRepository->countAllUsers();
        $userRegisterToday = $userRepository->registerToday();
        $outfitCount = $outfitRepository->countAllOutfits();
        $topAuthor = $outfitRepository->findTopAuthor();
        $outfitMostLikes = $outfitRepository->findOutfitWithMostLikes();
        $clothingItemsPerCategory = $categoryItemRepository->countClothingItemsPerCategory();
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }
        return $this->render('admin/index.html.twig', [
            'user_count' => $userCount,
            'user_register_today' => $userRegisterToday,
            'outfit_count' => $outfitCount,
            'top_author' => $topAuthor,
            'outfit_most_likes' => $outfitMostLikes,
            'clothing_items_per_category' => $clothingItemsPerCategory,
        ]);
    }
}
