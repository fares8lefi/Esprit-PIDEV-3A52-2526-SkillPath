<?php

namespace App\Controller\Front;

use App\Entity\Certificate;
use App\Entity\Cours;
use App\Entity\Module;
use App\Repository\CertificateRepository;
use App\Repository\CoursRepository;
use App\Repository\NotificationRepository;
use App\Repository\ModuleRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/front/cours', name: 'front_cours_')]
class CoursFrontController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, CoursRepository $coursRepository, NotificationRepository $notificationRepository): Response
    {
        $search = $request->query->get('search');
        $level = $request->query->get('level');
        $category = $request->query->get('category');
        $sort = $request->query->get('sort', 'date_desc');

        $cours = $coursRepository->findByFilters($search, $level, $category, $sort);
        $unreadCount = 0;

        if ($this->getUser()) {
            $unreadCount = $notificationRepository->countUnreadByUser($this->getUser());
        }
        $categoriesCount = $coursRepository->countByCategories();

        return $this->render('front/cours/index.html.twig', [
            'cours' => $cours,
            'categoriesCount' => $categoriesCount,
            'currentSearch' => $search,
            'currentLevel' => $level,
            'currentCategory' => $category,
            'currentSort' => $sort,
            'now' => new \DateTime(),
            'unreadCount' => $unreadCount,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Cours $cours, NotificationRepository $notificationRepository): Response
    {
        $user = $this->getUser();
        $progression = 0;
        
        if ($user) {
            $totalModules = count($cours->getModules());
            if ($totalModules > 0) {
                $completedInCours = 0;
                foreach ($cours->getModules() as $module) {
                    if ($user->getCompletedModules()->contains($module)) {
                        $completedInCours++;
                    }
                }
                $progression = ($completedInCours / $totalModules) * 100;
            }
        }

        $unreadCount = 0;
        if ($user) {
            $unreadCount = $notificationRepository->countUnreadByUser($user);
        }

        return $this->render('front/cours/show.html.twig', [
            'cours' => $cours,
            'progression' => $progression,
            'now' => new \DateTime(),
            'unreadCount' => $unreadCount,
        ]);
    }

    #[Route('/{coursId}/module/{moduleId}', name: 'module_show', methods: ['GET'])]
    public function showModule(int $coursId, int $moduleId, CoursRepository $coursRepository, NotificationRepository $notificationRepository): Response
    {
        $cours = $coursRepository->find($coursId);
        
        if (!$cours) {
            throw $this->createNotFoundException('Cours non trouvé');
        }

        $now = new \DateTime();
        $allModules = $cours->getModules();
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
                    return $this->redirectToRoute('front_cours_show', ['id' => $coursId]);
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

        $unreadCount = 0;
        if ($this->getUser()) {
            $unreadCount = $notificationRepository->countUnreadByUser($this->getUser());
        }

        return $this->render('front/cours/module.html.twig', [
            'cours' => $cours,
            'module' => $module,
            'previousModule' => $previousModule,
            'nextModule' => $nextModule,
            'moduleNumber' => $visibleIndex + 1,
            'totalModules' => $totalVisible,
            'isCompleted' => $this->getUser() ? $this->getUser()->getCompletedModules()->contains($module) : false,
            'unreadCount' => $unreadCount,
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

        return $this->redirectToRoute('front_cours_module_show', [
            'coursId' => $module->getCours()->getId(),
            'moduleId' => $module->getId()
        ]);
    }

    #[Route('/{coursId}/certificate/download', name: 'certificate_download', methods: ['GET'])]
    public function downloadCertificate(int $coursId, CoursRepository $coursRepository, EntityManagerInterface $em, CertificateRepository $certRepo): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $cours = $coursRepository->find($coursId);
        if (!$cours) {
            throw $this->createNotFoundException('Cours non trouvé.');
        }

        // Verification: 100% completion
        $totalModules = count($cours->getModules());
        $completedCount = 0;
        foreach ($cours->getModules() as $module) {
            if ($user->getCompletedModules()->contains($module)) {
                $completedCount++;
            }
        }

        if ($completedCount < $totalModules || $totalModules === 0) {
            $this->addFlash('error', 'Vous devez terminer tous les modules pour obtenir le certificat.');
            return $this->redirectToRoute('front_cours_show', ['id' => $coursId]);
        }

        // Find or Create Certificate
        $certificate = $certRepo->findOneBy(['user' => $user, 'cours' => $cours]);
        if (!$certificate) {
            $certificate = new Certificate();
            $certificate->setUser($user);
            $certificate->setCours($cours);
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
            'coursName' => $cours->getName(),
            'completionDate' => $certificate->getCreatedAt(),
            'certificateNumber' => $certificate->getCertificateNumber(),
            'sealPath' => $this->getParameter('kernel.project_dir') . '/public/images/academy_seal.png',
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $output = $dompdf->output();
        $filename = 'certificat-' . strtolower(str_replace(' ', '-', $cours->getName())) . '.pdf';

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
