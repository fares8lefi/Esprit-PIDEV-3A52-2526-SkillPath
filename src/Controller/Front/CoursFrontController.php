<?php

namespace App\Controller\Front;

use App\Entity\Cours;
use App\Repository\CoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/front/cours', name: 'front_courses_')]
class CoursFrontController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, CoursRepository $coursRepository): Response
    {
        $search = $request->query->get('search');
        $level = $request->query->get('level');
        $category = $request->query->get('category');
        $sort = $request->query->get('sort', 'date_desc');

        $courses = $coursRepository->findByFilters($search, $level, $category, $sort);
        $categoriesCount = $coursRepository->countByCategories();

        return $this->render('front/cours/index.html.twig', [
            'courses' => $courses,
            'categoriesCount' => $categoriesCount,
            'currentSearch' => $search,
            'currentLevel' => $level,
            'currentCategory' => $category,
            'currentSort' => $sort,
            'now' => new \DateTime(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Cours $course): Response
    {
        return $this->render('front/cours/show.html.twig', [
            'course' => $course,
            'now' => new \DateTime(),
        ]);
    }

    #[Route('/{courseId}/module/{moduleId}', name: 'module_show', methods: ['GET'])]
    public function showModule(int $courseId, int $moduleId, CoursRepository $coursRepository): Response
    {
        $course = $coursRepository->find($courseId);
        
        if (!$course) {
            throw $this->createNotFoundException('Cours non trouvé');
        }

        $now = new \DateTime();
        $allModules = $course->getModules();
        $visibleModules = $allModules->filter(function($module) use ($now) {
            return $module->getScheduledAt() <= $now;
        })->getValues();

        $module = null;
        $moduleIndex = -1;
        
        foreach ($allModules as $index => $m) {
            if ($m->getId() === $moduleId) {
                // Security check
                if ($m->getScheduledAt() > $now) {
                    $this->addFlash('info', 'Ce module sera disponible le ' . $m->getScheduledAt()->format('d/m/Y'));
                    return $this->redirectToRoute('front_courses_show', ['id' => $courseId]);
                }
                $module = $m;
                break;
            }
        }

        if (!$module) {
            throw $this->createNotFoundException('Module non trouvé');
        }

        // We need visibleIndex for navigation
        $visibleIndex = -1;
        foreach ($visibleModules as $idx => $vm) {
            if ($vm->getId() === $moduleId) {
                $visibleIndex = $idx;
                break;
            }
        }

        $totalVisible = count($visibleModules);
        $previousModule = $visibleIndex > 0 ? $visibleModules[$visibleIndex - 1] : null;
        $nextModule = $visibleIndex < $totalVisible - 1 ? $visibleModules[$visibleIndex + 1] : null;

        return $this->render('front/cours/module.html.twig', [
            'course' => $course,
            'module' => $module,
            'previousModule' => $previousModule,
            'nextModule' => $nextModule,
            'moduleNumber' => $visibleIndex + 1,
            'totalModules' => $totalVisible,
        ]);
    }
}
