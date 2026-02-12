<?php

namespace App\Controller\Admin;

use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin/course', name: 'admin_course_')]
class CourseController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        CourseRepository $courseRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search', '');
        
        $queryBuilder = $courseRepository->createQueryBuilder('c');

        if ($search) {
            $queryBuilder
                ->andWhere('c.title LIKE :search OR c.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder->orderBy('c.id', 'DESC');

        $courses = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('BackOffice/course/index.html.twig', [
            'courses' => $courses,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $original = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = $slugger->slug($original);
                $newName  = $safeName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('modules_upload_dir'), $newName);
                    $course->setImage($newName);
                } catch (FileException $e) {
                    $this->addFlash('error', "Upload failed.");
                }
            }

            $entityManager->persist($course);
            $entityManager->flush();

            $this->addFlash('success', 'Course created successfully.');

            return $this->redirectToRoute('admin_course_index');
        }

        return $this->render('BackOffice/course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Course $course): Response
    {
        return $this->render('BackOffice/course/show.html.twig', [
            'course' => $course,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Course $course, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $original = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = $slugger->slug($original);
                $newName  = $safeName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                     $imageFile->move($this->getParameter('modules_upload_dir'), $newName);
                     $course->setImage($newName);
                } catch (FileException $e) {
                    $this->addFlash('error', "Upload failed.");
                }
            }

            $course->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Course updated successfully.');

            return $this->redirectToRoute('admin_course_index');
        }

        return $this->render('BackOffice/course/edit.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Course $course, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $entityManager->remove($course);
            $entityManager->flush();
            $this->addFlash('success', 'Course deleted successfully.');
        }

        return $this->redirectToRoute('admin_course_index');
    }
}
