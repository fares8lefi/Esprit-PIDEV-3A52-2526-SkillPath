<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserRepository;
use App\Repository\CoursRepository;
use App\Repository\ModuleRepository;
class MainController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ModuleRepository $moduleRepository): Response
    {
        return $this->render('FrontOffice/main/index.html.twig', [
            'modules' => $moduleRepository->findAll(),
        ]);
    }

    #[Route('/admin', name: 'app_admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(UserRepository $userRepository,CoursRepository $coursRepository,ModuleRepository $moduleRepository
    ): Response
    {
        return $this->render('BackOffice/main/dashboard.html.twig', [
            'userCount' => $userRepository->count([]),
            'coursCount' => $coursRepository->count([]),
            'moduleCount' => $moduleRepository->count([]),
            'recentUsers' => $userRepository->findBy([], ['id' => 'DESC'], 3),
        ]);
    }

    #[Route('/redirect-home', name: 'app_home_redirect')]
    public function redirectHome(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin_dashboard');
        }

        return $this->redirectToRoute('app_home');
    }
}
