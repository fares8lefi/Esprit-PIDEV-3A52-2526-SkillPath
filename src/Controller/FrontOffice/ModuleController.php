<?php

namespace App\Controller\FrontOffice;

use App\Entity\Module;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/module')]
class ModuleController extends AbstractController
{
    #[Route('/{id}', name: 'app_front_office_module_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(?Module $module, \App\Service\UserCourseViewService $viewService): Response
    {
        if (!$module) {
            throw $this->createNotFoundException('Module non trouvé.');
        }
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder au contenu.');
            return $this->redirectToRoute('app_user_login');
        }

        $course = $module->getCourse();
        if (!$course) {
            throw $this->createNotFoundException('Ce module n\'est associé à aucun cours.');
        }

        if (!$viewService->isUserEnrolled($user, $course)) {
            $this->addFlash('warning', 'Vous devez vous inscrire à ce cours pour accéder à ses modules.');
            return $this->redirectToRoute('front_course_show', ['id' => $course->getId()]);
        }

        // Enregistrer la vue du module et mettre à jour l'index max
        $userView = $viewService->recordModuleView($user, $module);

        // S'assurer que l'utilisateur est bien marqué inscrit (correction des cas où le flag n'était pas positionné)
        if (!$userView->isEnrolled()) {
            $userView->setIsEnrolled(true);
        }

        return $this->render('FrontOffice/module/show.html.twig', [
            'module' => $module,
            'userCourseView' => $userView,
        ]);
    }

    #[Route('/{id}/heartbeat', name: 'app_front_office_module_heartbeat', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function heartbeat(?Module $module, \App\Service\UserCourseViewService $viewService): \Symfony\Component\HttpFoundation\JsonResponse
    {
        if (!$module) {
             return new \Symfony\Component\HttpFoundation\JsonResponse(['status' => 'error', 'message' => 'Module non trouvé'], 404);
        }
        $user = $this->getUser();
        if ($user instanceof \App\Entity\User) {
            $heartbeatCourse = $module->getCourse();
        if (!$heartbeatCourse) {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['status' => 'error', 'message' => 'Module sans cours'], 400);
        }
        $view = $viewService->recordView($user, $heartbeatCourse);
            $viewService->updateTimeSpent($view, 1); // Incrémente de 1 minute
            return new \Symfony\Component\HttpFoundation\JsonResponse(['status' => 'success', 'time' => $view->getTimeSpent()]);
        }
        return new \Symfony\Component\HttpFoundation\JsonResponse(['status' => 'error'], 403);
    }
}
