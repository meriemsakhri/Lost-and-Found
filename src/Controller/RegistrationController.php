<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): Response {
        $errors = [];

        if ($request->isMethod('POST')) {
            $name            = trim($request->request->get('name', ''));
            $email           = trim($request->request->get('email', ''));
            $phone           = trim($request->request->get('phone', ''));
            $password        = $request->request->get('password', '');
            $confirmPassword = $request->request->get('confirm_password', '');

            if ($name === '') {
                $errors[] = 'Name is required.';
            }

            if ($email === '') {
                $errors[] = 'Email is required.';
            } elseif ($userRepository->findOneBy(['email' => $email])) {
                $errors[] = 'This email is already registered.';
            }

            if ($password === '') {
                $errors[] = 'Password is required.';
            } elseif ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match.';
            }

            if (empty($errors)) {
                $user = new User();
                $user->setName($name);
                $user->setEmail($email);
                $user->setPhone($phone);

                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Account created! You can now log in.');

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/register.html.twig', [
            'errors' => $errors,
        ]);
    }
}
