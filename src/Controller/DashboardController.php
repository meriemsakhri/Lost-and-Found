<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, ItemRepository $itemRepository): Response
    {
        // Get query parameters from URL: /dashboard?q=wallet&category=Documents
        $q        = $request->query->get('q');
        $category = $request->query->get('category');

        $items      = $itemRepository->searchForDashboard($q, $category);
        $categories = $itemRepository->findDistinctCategories();

        return $this->render('dashboard/index.html.twig', [
            'items'             => $items,
            'search'            => $q,
            'selectedCategory'  => $category,
            'categories'        => $categories,
        ]);
    }
}
