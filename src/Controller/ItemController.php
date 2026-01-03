<?php

namespace App\Controller;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ItemController extends AbstractController
{
    #[Route('/items/new', name: 'app_item_new')]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $title        = trim($request->request->get('title', ''));
            $description  = trim($request->request->get('description', ''));
            $category     = trim($request->request->get('category', ''));
            $location     = trim($request->request->get('location', ''));
            $dateStr      = $request->request->get('date', '');
            $contactPhone = trim($request->request->get('contact_phone', ''));
            $contactEmail = trim($request->request->get('contact_email', ''));

            if ($title === '') {
                $errors[] = 'Title is required.';
            }
            if ($description === '') {
                $errors[] = 'Description is required.';
            }
            if ($location === '') {
                $errors[] = 'Location is required.';
            }
            if ($contactEmail === '') {
                $errors[] = 'Contact email is required.';
            }

            if ($dateStr) {
                $date = new \DateTimeImmutable($dateStr);
            } else {
                $date = new \DateTimeImmutable();
            }

            // Image upload
            $imageFile = $request->files->get('image');
            $imageFilename = null;

            if ($imageFile) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads';

                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0777, true);
                }

                $ext = $imageFile->guessExtension() ?: 'bin';
                $imageFilename = uniqid('item_') . '.' . $ext;
                $imageFile->move($uploadsDir, $imageFilename);
            }

            if (empty($errors)) {
                /** @var \App\Entity\User $user */
                $user = $this->getUser();

                $item = new Item();
                $item->setTitle($title);
                $item->setDescription($description);
                $item->setCategory($category);
                $item->setLocation($location);
                $item->setDate($date);
                $item->setContactPhone($contactPhone ?: $user->getPhone());
                $item->setContactEmail($contactEmail ?: $user->getEmail());
                $item->setOwner($user);
                $item->setType('lost');
                $item->setStatus('active');

                if ($imageFilename) {
                    $item->setImage($imageFilename);
                }

                $em->persist($item);
                $em->flush();

                $this->addFlash('success', 'Lost item reported successfully!');

                return $this->redirectToRoute('app_dashboard');
            }
        }

        return $this->render('item/new.html.twig', [
            'errors' => $errors,
        ]);
    }

    #[Route('/items/{id}/edit', name: 'app_item_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ItemRepository $itemRepository
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $item = $itemRepository->find($id);
        if (!$item) {
            throw $this->createNotFoundException('Item not found.');
        }

        if ($item->getOwner() !== $user) {
            throw $this->createAccessDeniedException('You are not allowed to edit this item.');
        }

        $errors = [];

        if ($request->isMethod('POST')) {
            $title        = trim($request->request->get('title', ''));
            $description  = trim($request->request->get('description', ''));
            $category     = trim($request->request->get('category', ''));
            $location     = trim($request->request->get('location', ''));
            $dateStr      = $request->request->get('date', '');
            $contactPhone = trim($request->request->get('contact_phone', ''));
            $contactEmail = trim($request->request->get('contact_email', ''));

            if ($title === '') {
                $errors[] = 'Title is required.';
            }
            if ($description === '') {
                $errors[] = 'Description is required.';
            }
            if ($location === '') {
                $errors[] = 'Location is required.';
            }
            if ($contactEmail === '') {
                $errors[] = 'Contact email is required.';
            }

            if ($dateStr) {
                $date = new \DateTimeImmutable($dateStr);
            } else {
                $date = $item->getDate() ?: new \DateTimeImmutable();
            }

            // Image upload (optional, replace old one)
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads';

                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0777, true);
                }

                $ext = $imageFile->guessExtension() ?: 'bin';
                $imageFilename = uniqid('item_') . '.' . $ext;
                $imageFile->move($uploadsDir, $imageFilename);

                $item->setImage($imageFilename);
            }

            if (empty($errors)) {
                $item->setTitle($title);
                $item->setDescription($description);
                $item->setCategory($category);
                $item->setLocation($location);
                $item->setDate($date);
                $item->setContactPhone($contactPhone ?: $user->getPhone());
                $item->setContactEmail($contactEmail ?: $user->getEmail());

                $em->flush();

                $this->addFlash('success', 'Item updated successfully.');

                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('item/edit.html.twig', [
            'item'   => $item,
            'errors' => $errors,
        ]);
    }

    #[Route('/items/{id}/return', name: 'app_item_mark_returned', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markReturned(
        int $id,
        ItemRepository $itemRepository,
        EntityManagerInterface $em
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $item = $itemRepository->find($id);
        if (!$item) {
            throw $this->createNotFoundException('Item not found.');
        }

        if ($item->getOwner() !== $user) {
            throw $this->createAccessDeniedException('You are not allowed to modify this item.');
        }

        $item->setStatus('returned');
        $em->flush();

        $this->addFlash('success', 'Item marked as returned.');

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/items/{id}/reopen', name: 'app_item_mark_active', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markActive(
        int $id,
        ItemRepository $itemRepository,
        EntityManagerInterface $em
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $item = $itemRepository->find($id);
        if (!$item) {
            throw $this->createNotFoundException('Item not found.');
        }

        if ($item->getOwner() !== $user) {
            throw $this->createAccessDeniedException('You are not allowed to modify this item.');
        }

        $item->setStatus('active');
        $em->flush();

        $this->addFlash('success', 'Item moved back to active.');

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/items/{id}/delete', name: 'app_item_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(
        int $id,
        ItemRepository $itemRepository,
        EntityManagerInterface $em
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $item = $itemRepository->find($id);
        if (!$item) {
            throw $this->createNotFoundException('Item not found.');
        }

        if ($item->getOwner() !== $user) {
            throw $this->createAccessDeniedException('You are not allowed to delete this item.');
        }

        $em->remove($item);
        $em->flush();

        $this->addFlash('success', 'Item deleted.');

        return $this->redirectToRoute('app_profile');
    }
}
