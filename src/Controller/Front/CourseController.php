<?php

namespace App\Controller\Front;

use App\Entity\Course;
use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/course', name: 'front_course_')]
class CourseController extends AbstractController
{

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Course $course, \App\Service\UserCourseViewService $viewService): Response
    {
        $user = $this->getUser();
        $isEnrolled = false;
        
        if ($user instanceof \App\Entity\User) {
            $viewService->recordView($user, $course);
            $isEnrolled = $viewService->isUserEnrolled($user, $course);
        }

        return $this->render('FrontOffice/course/show.html.twig', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
        ]);
    }

    #[Route('/{id}/enroll', name: 'enroll', methods: ['POST', 'GET'])]
    public function enroll(Course $course, \App\Service\UserCourseViewService $viewService): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            $this->addFlash('error', 'Vous devez être connecté pour vous inscrire.');
            return $this->redirectToRoute('app_login');
        }

        $viewService->enrollUser($user, $course);
        $this->addFlash('success', 'Félicitations ! Vous êtes maintenant inscrit au cours : ' . $course->getTitle());

        return $this->redirectToRoute('front_course_show', ['id' => $course->getId()]);
    }

    #[Route('/my-courses', name: 'my_courses', methods: ['GET'])]
    public function myCourses(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_user_login');
        }

        return $this->render('FrontOffice/course/my_courses.html.twig', [
            'courses' => $user->getCourses(),
        ]);
    }
}
