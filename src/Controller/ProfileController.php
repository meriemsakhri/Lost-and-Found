<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function index(ItemRepository $itemRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Items belonging to this user
        $activeItems = $itemRepository->findBy(
            ['owner' => $user, 'status' => 'active'],
            ['date' => 'DESC']
        );

        $returnedItems = $itemRepository->findBy(
            ['owner' => $user, 'status' => 'returned'],
            ['date' => 'DESC']
        );

        return $this->render('profile/index.html.twig', [
            'user'          => $user,
            'activeItems'   => $activeItems,
            'returnedItems' => $returnedItems,
        ]);
    }
}
