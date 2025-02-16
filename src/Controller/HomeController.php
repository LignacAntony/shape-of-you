<?php

namespace App\Controller;

use App\Repository\OutfitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function __invoke(Request $request, OutfitRepository $outfitRepository): Response
    {
        $sortBy = $request->query->get('sort_by', 'recent');
        $search = $request->query->get('search', '');

        $orderByField = match ($sortBy) {
            'recent' => 'o.createdAt',
            'oldest' => 'o.createdAt',
            'most_likes' => 'o.likesCount',
            'least_likes' => 'o.likesCount',
            default => 'o.createdAt',
        };
        $orderDirection = in_array($sortBy, ['oldest', 'least_likes']) ? 'ASC' : 'DESC';

        $queryBuilder = $outfitRepository->createQueryBuilder('o')
            ->where('o.isPublished = :published')
            ->setParameter('published', true);

        if (!empty($search)) {
            $queryBuilder->andWhere('o.name LIKE :search OR o.description LIKE :search')
                ->setParameter('search', "%$search%");
        }

        $outfits = $queryBuilder
            ->orderBy($orderByField, $orderDirection)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('index.html.twig', [
            'outfits' => $outfits,
            'sort_by' => $sortBy,
            'search' => $search
        ]);
    }
}
