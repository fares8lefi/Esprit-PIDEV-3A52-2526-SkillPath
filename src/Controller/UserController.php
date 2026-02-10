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
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mime\Email;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/login', name: 'app_user_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('FrontOffice/user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_user_register', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer): Response
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

            $user->setStatus('pending'); 
            $user->setRole('student'); 
            
            // Générer un code de vérification aléatoire à 6 chiffres
            $verificationCode = sprintf('%06d', random_int(0, 999999));
            echo "==========================================================================================================\n";
            dump($verificationCode); //a affichage dan,s le terminal 
            $user->setVerificationCode($verificationCode);

            $entityManager->persist($user);
            $entityManager->flush();

            // Log pour déboguer
            $userEmail = $user->getEmail();
            
            // Envoyer l'email de vérification
            try {
                // Vérifier que l'email existe
                if (empty($userEmail)) {
                    throw new \Exception('L\'adresse email de l\'utilisateur est vide');
                }
                
                $email = (new Email())
                    ->from('skillPathdonotreply@gmail.com')
                    ->to($userEmail)
                    ->subject('Vérification de votre compte SkillPath')
                    ->html(
                        '<h1>Bienvenue sur SkillPath!</h1>' .
                        '<p>Bonjour <strong>' . $user->getUsername() . '</strong>,</p>' .
                        '<p>Votre code de vérification est : <strong style="font-size: 24px; color: #1e88e5;">' . $verificationCode . '</strong></p>' .
                        '<p>Veuillez entrer ce code pour activer votre compte.</p>' .
                        '<p>Ce code est valable pendant 24 heures.</p>' .
                        '<p style="color: #666; font-size: 12px;">Si vous n\'avez pas créé de compte, ignorez cet email.</p>'
                    );

                $mailer->send($email);
                
                $this->addFlash('success', 'Un email de vérification a été envoyé à ' . $userEmail);
            } catch (\Exception $e) {
                // En cas d'erreur, on affiche le message mais on permet quand même la vérification
                $this->addFlash('warning', 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage() . '. Votre code de vérification est : ' . $verificationCode);
            }

            // Rediriger vers la page de vérification
            return $this->redirectToRoute('app_user_verify', ['email' => $userEmail]);
        }

        return $this->render('FrontOffice/user/register.html.twig', [
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

        return $this->render('BackOffice/user/create.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(User $user): Response
    {
        return $this->render('BackOffice/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $user->setEmail($request->request->get('email'));
            $user->setUsername($request->request->get('username'));
            
            $role = $request->request->get('role');
            if (in_array($role, ['student', 'admin'])) {
                $user->setRole($role);
            }

            $user->setStatus($request->request->get('status'));

            // Optionnel: modification du mot de passe
            $newPassword = $request->request->get('password');
            if (!empty($newPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            }

            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('BackOffice/user/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_user_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('BackOffice/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/verify', name: 'app_user_verify', methods: ['GET', 'POST'])]
    public function verify(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        // Récupérer l'email depuis GET ou POST
        $email = $request->query->get('email');
        
        // Si POST, traiter la vérification
        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email'));
            $code = trim($request->request->get('code'));
            echo "============================================================================================================\n";

            dump($code);
            
            
            // Trouver l'utilisateur
            $user = $userRepository->findOneBy(['email' => $email]);
            
            if ($user === null) {
                $this->addFlash('error', 'Aucun compte trouvé avec cet email.');
                return $this->redirectToRoute('app_user_verify', ['email' => $email]);
            }
            
            // Vérifier si déjà vérifié
            if ($user->isVerified()) {
                $this->addFlash('success', 'Votre compte est déjà vérifié !');
                return $this->redirectToRoute('app_user_login');
            }
            
            // Comparer les codes (avec trim pour éviter les espaces)
            $storedCode = trim($user->getVerificationCode() ?? '');
            echo "============================================================================================================\n";
            dump($storedCode);
            $inputCode = trim($code);
            echo "============================================================================================================\n";
            dump($inputCode);
            
            // Comparaison
            if (!empty($storedCode) && $storedCode === $inputCode) {
                // Code correct - activer le compte
                $user->setIsVerified(true);
                $user->setStatus('active');
                $user->setVerificationCode(null);
                $entityManager->flush();
                
                $this->addFlash('success', 'Votre compte a été vérifié avec succès ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_user_login');
            } else {
                // Code incorrect - afficher les valeurs pour debug
                $this->addFlash('error', 'Code incorrect. Stocké: "' . $storedCode . '" (len:' . strlen($storedCode) . ') - Entré: "' . $inputCode . '" (len:' . strlen($inputCode) . ')');
                return $this->redirectToRoute('app_user_verify', ['email' => $email]);
            }
        }
        
        // Afficher la page de vérification (GET)
        return $this->render('FrontOffice/user/verify.html.twig', [
            'email' => $email,
        ]);
    }

    #[Route('/resend-verification', name: 'app_user_resend_verification', methods: ['POST'])]
    public function resendVerification(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, MailerInterface $mailer): Response
    {
        $email = $request->request->get('email');
        $user = $userRepository->findOneBy(['email' => $email]);

        if ($user && !$user->isVerified()) {
            // Générer un nouveau code
            $verificationCode = sprintf('%06d', random_int(0, 999999));
            $user->setVerificationCode($verificationCode);
            $entityManager->flush();

            // Envoyer l'email
            $emailMessage = (new Email())
                ->from('skillPathdonotreply@gmail.com')
                ->to($user->getEmail())
                ->subject('Nouveau code de vérification SkillPath')
                ->html(
                    '<h1>Nouveau code de vérification</h1>' .
                    '<p>Votre nouveau code de vérification est : <strong>' . $verificationCode . '</strong></p>' .
                    '<p>Veuillez entrer ce code pour activer votre compte.</p>'
                );

            $mailer->send($emailMessage);
            
            $this->addFlash('success', 'Un nouveau code a été envoyé à votre email.');
        } else {
            $this->addFlash('error', 'Compte introuvable ou déjà vérifié.');
        }

    }

    #[Route('/profile', name: 'app_user_profile', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $currentPassword = $request->request->get('current_password');
            $newUsername = $request->request->get('username');
            $newEmail = $request->request->get('email');
            $newPassword = $request->request->get('new_password');

            // 1. Vérifier le mot de passe actuel
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                return $this->redirectToRoute('app_user_profile');
            }

            // 2. Mettre à jour les informations de base
            $user->setUsername($newUsername);
            $user->setEmail($newEmail);

            // 3. Mettre à jour le mot de passe si fourni
            if (!empty($newPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
            
            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('FrontOffice/user/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
