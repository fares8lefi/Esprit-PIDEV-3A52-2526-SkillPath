<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserRepository;
use App\Repository\CourseRepository;
use App\Repository\ModuleRepository;
use App\Repository\ReclamationRepository;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CourseRepository $courseRepository): Response
    {
        return $this->render('FrontOffice/main/index.html.twig', [
            'courses' => $courseRepository->findAll(),
        ]);
    }

    #[Route('/admin', name: 'app_admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(
        UserRepository $userRepository,
        CourseRepository $courseRepository,
        ModuleRepository $moduleRepository,
        ReclamationRepository $reclamationRepository
    ): Response {
        $reclamations = $reclamationRepository->findAll();
        $pendingCount = count(array_filter($reclamations, fn($r) => in_array(strtolower($r->getStatut()), ['pending', 'en attente'])));
        $resolvedCount = count(array_filter($reclamations, fn($r) => in_array(strtolower($r->getStatut()), ['resolved', 'résolu'])));

        return $this->render('BackOffice/main/dashboard.html.twig', [
            'userCount'      => $userRepository->count([]),
            'courseCount'    => $courseRepository->count([]),
            'moduleCount'    => $moduleRepository->count([]),
            'reclamationCount' => count($reclamations),
            'pendingCount'   => $pendingCount,
            'resolvedCount'  => $resolvedCount,
            'recentUsers'    => $userRepository->findBy([], ['id' => 'DESC'], 5),
            'recentReclamations' => $reclamationRepository->findBy([], ['id' => 'DESC'], 4),
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
