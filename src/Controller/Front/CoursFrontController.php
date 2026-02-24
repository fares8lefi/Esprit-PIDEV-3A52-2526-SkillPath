<?php

namespace App\Controller\Front;

use App\Entity\Certificate;
use App\Entity\Cours;
use App\Entity\Module;
use App\Repository\CertificateRepository;
use App\Repository\CoursRepository;
use App\Repository\ModuleRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
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
        $user = $this->getUser();
        $progression = 0;
        
        if ($user) {
            $totalModules = count($course->getModules());
            if ($totalModules > 0) {
                $completedInCourse = 0;
                foreach ($course->getModules() as $module) {
                    if ($user->getCompletedModules()->contains($module)) {
                        $completedInCourse++;
                    }
                }
                $progression = ($completedInCourse / $totalModules) * 100;
            }
        }

        return $this->render('front/cours/show.html.twig', [
            'course' => $course,
            'progression' => $progression,
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
            'isCompleted' => $this->getUser() ? $this->getUser()->getCompletedModules()->contains($module) : false,
        ]);
    }

    #[Route('/module/{id}/complete', name: 'module_complete', methods: ['POST'])]
    public function completeModule(Module $module, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 403);
        }

        if (!$user->getCompletedModules()->contains($module)) {
            $user->addCompletedModule($module);
            $em->flush();
        }

        return $this->redirectToRoute('front_courses_module_show', [
            'courseId' => $module->getCours()->getId(),
            'moduleId' => $module->getId()
        ]);
    }

    #[Route('/{courseId}/certificate/download', name: 'certificate_download', methods: ['GET'])]
    public function downloadCertificate(int $courseId, CoursRepository $coursRepository, EntityManagerInterface $em, CertificateRepository $certRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $course = $coursRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Cours non trouvé.');
        }

        // Verification: 100% completion
        $totalModules = count($course->getModules());
        $completedCount = 0;
        foreach ($course->getModules() as $module) {
            if ($user->getCompletedModules()->contains($module)) {
                $completedCount++;
            }
        }

        if ($completedCount < $totalModules || $totalModules === 0) {
            $this->addFlash('error', 'Vous devez terminer tous les modules pour obtenir le certificat.');
            return $this->redirectToRoute('front_courses_show', ['id' => $courseId]);
        }

        // Find or Create Certificate
        $certificate = $certRepo->findOneBy(['user' => $user, 'course' => $course]);
        if (!$certificate) {
            $certificate = new Certificate();
            $certificate->setUser($user);
            $certificate->setCourse($course);
            $certificate->setCertificateNumber('SKP-' . strtoupper(uniqid()));
            $em->persist($certificate);
            $em->flush();
        }

        // PDF Generation
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('front/certificate/pdf.html.twig', [
            'studentName' => method_exists($user, 'getFirstName') && $user->getFirstName() ? $user->getFirstName() . ' ' . $user->getLastName() : ($user->getUserIdentifier() ?: $user->getEmail()),
            'courseName' => $course->getName(),
            'completionDate' => $certificate->getCreatedAt(),
            'certificateNumber' => $certificate->getCertificateNumber(),
            'sealPath' => $this->getParameter('kernel.project_dir') . '/public/images/academy_seal.png',
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $output = $dompdf->output();
        $filename = 'certificat-' . strtolower(str_replace(' ', '-', $course->getName())) . '.pdf';

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
