<?php

namespace App\Controller\Front;

use App\Entity\Course;
use App\Entity\User;
use App\Repository\CourseRepository;
use App\Service\PredictionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/course', name: 'front_course_')]
class CourseController extends AbstractController
{
    private PredictionService $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request, 
        CourseRepository $courseRepository,
        \App\Repository\CertificateRepository $certificateRepository
    ): Response {
        $search = $request->query->get('search');
        $level = $request->query->get('level');
        $category = $request->query->get('category');
        $sort = $request->query->get('sort', 'recent');

        $courses = $courseRepository->findByFilters($search, $level, $category, $sort);
        $categoriesCount = $courseRepository->countByCategories();

        // Calculer les scores IA et vérifier les certifications si l'utilisateur est connecté
        $aiScores = [];
        $certifiedCourseIds = [];
        $user = $this->getUser();
        
        if ($user instanceof User) {
            foreach ($courses as $course) {
                $aiScores[$course->getId()] = $this->predictionService->predictSuccessProbability($user, $course);
            }

            // Récupérer les IDs des cours pour lesquels l'utilisateur a un certificat
            $userCertificates = $certificateRepository->findBy(['user' => $user]);
            foreach ($userCertificates as $cert) {
                if ($cert->getCourse()) {
                    $certifiedCourseIds[] = $cert->getCourse()->getId();
                }
            }
        }

        return $this->render('FrontOffice/course/index.html.twig', [
            'courses' => $courses,
            'categoriesCount' => $categoriesCount,
            'currentSearch' => $search,
            'currentLevel' => $level,
            'currentCategory' => $category,
            'currentSort' => $sort,
            'aiScores' => $aiScores,
            'certifiedCourseIds' => $certifiedCourseIds,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Course $course, \App\Service\UserCourseViewService $viewService, \Doctrine\ORM\EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $isEnrolled = false;
        $isCompleted = false;
        $isCertified = false;
        
        if ($user instanceof \App\Entity\User) {
            $view = $viewService->recordView($user, $course);
            $isEnrolled = $viewService->isUserEnrolled($user, $course);
            $isCompleted = $view->isCompleted();
            $isCertified = $em->getRepository(\App\Entity\Certificate::class)->findOneBy(['user' => $user, 'course' => $course]) !== null;
        }

        return $this->render('FrontOffice/course/show.html.twig', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'isCompleted' => $isCompleted,
            'isCertified' => $isCertified,
        ]);
    }

    #[Route('/{id}/enroll', name: 'enroll', methods: ['POST', 'GET'])]
    public function enroll(Course $course, \App\Service\UserCourseViewService $viewService): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            $this->addFlash('error', 'Vous devez être connecté pour vous inscrire.');
            return $this->redirectToRoute('app_user_login');
        }

        $viewService->enrollUser($user, $course);
        $this->addFlash('success', 'Félicitations ! Vous êtes maintenant inscrit au cours : ' . $course->getTitle());

        return $this->redirectToRoute('front_course_show', ['id' => $course->getId()]);
    }

    #[Route('/my-courses', name: 'my_courses', methods: ['GET'])]
    public function myCourses(\App\Repository\UserCourseViewRepository $repository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_user_login');
        }

        return $this->render('FrontOffice/course/my_courses.html.twig', [
            'views' => $repository->findByUser($user->getId()),
        ]);
    }

    #[Route('/{id}/complete', name: 'complete', methods: ['POST'])]
    public function complete(Course $course, \App\Service\UserCourseViewService $viewService): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['status' => 'error', 'message' => 'Non authentifié'], 403);
        }

        $viewService->markCourseAsCompleted($user, $course);
        return new \Symfony\Component\HttpFoundation\JsonResponse(['status' => 'success']);
    }

    #[Route('/{id}/certificate/download', name: 'certificate_download', methods: ['GET'])]
    public function downloadCertificate(
        Course $course, 
        \App\Service\UserCourseViewService $viewService,
        \App\Service\CertificateService $certificateService,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_user_login');
        }

        // Vérifier si complété
        $view = $entityManager->getRepository(\App\Entity\UserCourseView::class)->findOneBy(['user' => $user, 'course' => $course]);
        if (!$view || !$view->isCompleted()) {
            $this->addFlash('error', "Vous n'avez pas encore terminé ce cours.");
            return $this->redirectToRoute('front_course_show', ['id' => $course->getId()]);
        }

        // Chercher ou créer le certificat
        $certRepo = $entityManager->getRepository(\App\Entity\Certificate::class);
        $certificate = $certRepo->findOneBy(['user' => $user, 'course' => $course]);

        if (!$certificate) {
            $certificate = new \App\Entity\Certificate();
            $certificate->setUser($user);
            $certificate->setCourse($course);
            $certificate->setCertCode('CERT-' . $user->getId() . '-' . $course->getId() . '-' . date('Y'));
            $entityManager->persist($certificate);
            $entityManager->flush();
        }

        // Générer PDF
        $pdfContent = $certificateService->generatePdfContent($certificate);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Certificat_' . $course->getId() . '.pdf"'
        ]);
    }
}
