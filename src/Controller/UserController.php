<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/login', name: 'app_user_login', methods: ['GET', 'POST'])]
    public function login(): Response
    {
        return $this->render('user/login.html.twig');
    }

    #[Route('/register', name: 'app_user_register', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        // Simulation d'un formulaire pour l'exemple
        if ($request->isMethod('POST')) {
            $user->setEmail($request->request->get('email'));
            $user->setUsername($request->request->get('username'));
            
            // Hachage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $request->request->get('password')
            );
            $user->setPassword($hashedPassword);

            $user->setStatus('active');
            $user->setRole('student'); // Rôle par défaut

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/register.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/create', name: 'app_user_admin_create', methods: ['GET', 'POST'])]
    public function adminCreate(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        
        if ($request->isMethod('POST')) {
            $user->setEmail($request->request->get('email'));
            $user->setUsername($request->request->get('username'));
            
            // Hachage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $request->request->get('password')
            );
            $user->setPassword($hashedPassword);

            $user->setStatus('active');
            
            // L'admin choisit le rôle
            $role = $request->request->get('role');
            if (in_array($role, ['student', 'admin'])) {
                $user->setRole($role);
            } else {
                $user->setRole('student');
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/admin_create.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
}
