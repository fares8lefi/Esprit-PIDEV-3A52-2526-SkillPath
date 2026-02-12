<?php

namespace App\Controller\Front;

use App\Entity\Cours;
use App\Repository\CoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/front/cours', name: 'front_courses_')]
class CoursFrontController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CoursRepository $coursRepository): Response
    {
        // Renaming variable 'modules' to 'courses' for the template
        return $this->render('front/cours/index.html.twig', [
            'courses' => $coursRepository->findAll(),
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
}
