<?php

namespace App\Controller;

use App\Service\RecommendationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class RecommendationController extends AbstractController
{
    #[Route('/recommendations', name: 'app_recommendations')]
    public function index(Request $request, RecommendationService $recommendationService): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $budget = $request->query->get('budget');
        if ($budget !== null && is_numeric($budget)) {
            $budget = (float) $budget;
        } else {
            $budget = null;
        }

        $recommendations = $recommendationService->recommendCourses($user, $budget);

        return $this->render('recommendations/index.html.twig', [
            'recommendations' => $recommendations,
            'budget' => $budget
        ]);
    }
}
