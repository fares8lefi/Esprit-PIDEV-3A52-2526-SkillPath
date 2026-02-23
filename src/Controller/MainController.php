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
use App\Repository\EventRepository;
use App\Repository\ResultatRepository;

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
        ReclamationRepository $reclamationRepository,
        EventRepository $eventRepository,
        ResultatRepository $resultatRepository
    ): Response {
        $users = $userRepository->findAll();
        
        // Roles Data
        $rolesData = ['admin' => 0, 'user' => 0];
        foreach ($users as $user) {
            $role = strtolower($user->getRole() ?? 'user');
            if (isset($rolesData[$role])) {
                $rolesData[$role]++;
            } else {
                $rolesData['user']++;
            }
        }

        // Categories Data
        $courses = $courseRepository->findAll();
        $categoriesData = [];
        foreach ($courses as $course) {
            $cat = $course->getCategory() ?: 'Non classé';
            $categoriesData[$cat] = ($categoriesData[$cat] ?? 0) + 1;
        }

        // Reclamation Data — statuses match ReclamationStatusType form values
        $reclamations = $reclamationRepository->findAll();
        $reclamationStats = ['Pending' => 0, 'In Progress' => 0, 'Resolved' => 0, 'Closed' => 0];
        foreach ($reclamations as $reclamation) {
            $status = $reclamation->getStatut() ?: 'Pending';
            if (isset($reclamationStats[$status])) {
                $reclamationStats[$status]++;
            } else {
                
                $map = ['En attente' => 'Pending', 'En cours' => 'In Progress', 'Traité' => 'Resolved'];
                $mapped = $map[$status] ?? 'Pending';
                $reclamationStats[$mapped]++;
            }
        }

        // Registration Data (Last 7 days)
        $registrationData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = (new \DateTime())->modify("-$i days")->format('Y-m-d');
            $registrationData[$date] = 0;
        }
        foreach ($users as $user) {
            if ($user->getCreatedAt()) {
                $date = $user->getCreatedAt()->format('Y-m-d');
                if (isset($registrationData[$date])) {
                    $registrationData[$date]++;
                }
            }
        }

        return $this->render('BackOffice/main/dashboard.html.twig', [
            'userCount' => count($users),
            'courseCount' => count($courses),
            'moduleCount' => $moduleRepository->count([]),
            'reclamationCount' => count($reclamations),
            'eventCount' => $eventRepository->count([]),
            'resultatCount' => $resultatRepository->count([]),
            'recentUsers' => $userRepository->findBy([], ['id' => 'DESC'], 5),
            
            // Chart Data - Formatted for Twig/JS
            'rolesLabels' => array_keys($rolesData),
            'rolesValues' => array_values($rolesData),
            'categoriesLabels' => array_keys($categoriesData),
            'categoriesValues' => array_values($categoriesData),
            'reclamationLabels' => array_keys($reclamationStats),
            'reclamationValues' => array_values($reclamationStats),
            'registrationLabels' => array_keys($registrationData),
            'registrationValues' => array_values($registrationData),
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
