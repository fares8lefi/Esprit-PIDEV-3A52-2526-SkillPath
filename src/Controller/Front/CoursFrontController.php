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
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Cours $course): Response
    {
        // Renaming variable 'module' to 'course' for the template
        return $this->render('front/cours/show.html.twig', [
            'course' => $course,
        ]);
    }

    #[Route('/{courseId}/module/{moduleId}', name: 'module_show', methods: ['GET'])]
    public function showModule(int $courseId, int $moduleId, CoursRepository $coursRepository): Response
    {
        $course = $coursRepository->find($courseId);
        
        if (!$course) {
            throw $this->createNotFoundException('Cours non trouvé');
        }

        $module = null;
        $moduleIndex = 0;
        $totalModules = $course->getModules()->count();

        foreach ($course->getModules() as $index => $m) {
            if ($m->getId() === $moduleId) {
                $module = $m;
                $moduleIndex = $index;
                break;
            }
        }

        if (!$module) {
            throw $this->createNotFoundException('Module non trouvé');
        }

        // Get previous and next modules
        $previousModule = $moduleIndex > 0 ? $course->getModules()[$moduleIndex - 1] : null;
        $nextModule = $moduleIndex < $totalModules - 1 ? $course->getModules()[$moduleIndex + 1] : null;

        return $this->render('front/cours/module.html.twig', [
            'course' => $course,
            'module' => $module,
            'previousModule' => $previousModule,
            'nextModule' => $nextModule,
            'moduleNumber' => $moduleIndex + 1,
            'totalModules' => $totalModules,
        ]);
    }
}
