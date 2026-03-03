<?php

namespace App\Controller\FrontOffice;

use App\Entity\Module;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/module')]
class ModuleController extends AbstractController
{
    #[Route('/{id}', name: 'app_front_office_module_show', methods: ['GET'])]
    public function show(Module $module, \App\Service\UserCourseViewService $viewService): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder au contenu.');
            return $this->redirectToRoute('app_user_login');
        }

        if (!$viewService->isUserEnrolled($user, $module->getCourse())) {
            $this->addFlash('warning', 'Vous devez vous inscrire à ce cours pour accéder à ses modules.');
            return $this->redirectToRoute('front_course_show', ['id' => $module->getCourse()->getId()]);
        }

        // Enregistrer la vue du module et mettre à jour l'index max
        $userView = $viewService->recordModuleView($user, $module);

        return $this->render('FrontOffice/module/show.html.twig', [
            'module' => $module,
            'userCourseView' => $userView,
        ]);
    }

    #[Route('/{id}/heartbeat', name: 'app_front_office_module_heartbeat', methods: ['POST'])]
    public function heartbeat(Module $module, \App\Service\UserCourseViewService $viewService): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $user = $this->getUser();
        if ($user instanceof \App\Entity\User) {
            $view = $viewService->recordView($user, $module->getCourse());
            $viewService->updateTimeSpent($view, 1); // Incrémente de 1 minute
            return new \Symfony\Component\HttpFoundation\JsonResponse(['status' => 'success', 'time' => $view->getTimeSpent()]);
        }
        return new \Symfony\Component\HttpFoundation\JsonResponse(['status' => 'error'], 403);
    }
}
