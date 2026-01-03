<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ItemRepository $itemRepository): Response
    {
        // All items (any status) – for total count
        $allItems = $itemRepository->findBy([], ['date' => 'DESC']);

        // Currently lost = type "lost" + status "active"
        $lostItems = $itemRepository->findBy(
            ['type' => 'lost', 'status' => 'active'],
            ['date' => 'DESC']
        );

        // Successfully found = items with status "returned"
        $foundItems = $itemRepository->findBy(
            ['status' => 'returned'],
            ['date' => 'DESC']
        );

        return $this->render('home/index.html.twig', [
            'allItems'           => $allItems,
            'lostItems'          => $lostItems,
            'foundItems'         => $foundItems,
            'itemsReported'      => \count($allItems),
            'currentlyLostCount' => \count($lostItems),
            'successfullyFound'  => \count($foundItems),
        ]);
    }
}
