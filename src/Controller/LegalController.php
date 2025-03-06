<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    #[Route('/conditions-utilisation', name: 'app_terms')]
    public function terms(): Response
    {
        return $this->render('legal/terms.html.twig');
    }

    #[Route('/test-403', name: 'test_403')]
    public function test403(): Response
    {
        throw $this->createAccessDeniedException('Test page 403');
    }

    #[Route('/test-404', name: 'test_404')]
    public function test404(): Response
    {
        throw $this->createNotFoundException('Test page 404');
    }
} 