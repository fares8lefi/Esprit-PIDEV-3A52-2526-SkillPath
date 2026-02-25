<?php

namespace App\Controller;

use App\Entity\Module;
use App\Form\ModuleType;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\NotificationService;

#[Route('/admin/modules', name: 'admin_module_')]
class ModuleController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(
        Request $request,
        ModuleRepository $moduleRepository,
        \App\Repository\CoursRepository $coursRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search', '');
        $coursId = $request->query->get('cours');

        $cours = $coursRepository->findBy([], ['titre' => 'ASC']);

        $qb = $moduleRepository->qbSearch($search, $coursId ? (int) $coursId : null);

        $modules = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('BackOffice/module/list.html.twig', [
            'modules' => $modules,
            'cours' => $cours,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        NotificationService $notificationService
    ): Response {
        $module = new Module();
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Image logic removed (moved to Cours)
            
            $em->persist($module);
            $em->flush();

            // Notify students
            $notificationService->notifyNewContent('module', $module->getName());

            $this->addFlash('success', 'Module ajouté avec succès.');
            return $this->redirectToRoute('admin_module_list');
        }

        return $this->render('BackOffice/module/new.html.twig', [
            'form' => $form,
            'module' => $module,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Module $module): Response
    {
        return $this->render('BackOffice/module/show.html.twig', [
            'module' => $module,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Module $module,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Image logic removed

            $em->flush();

            $this->addFlash('success', 'Module modifié avec succès.');
            return $this->redirectToRoute('admin_module_list');
        }

        return $this->render('BackOffice/module/edit.html.twig', [
            'form' => $form,
            'module' => $module,
        ]);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(
        Request $request,
        Module $module,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete_module' . $module->getId(), $request->request->get('_token'))) {
            $em->remove($module);
            $em->flush();
            $this->addFlash('success', 'Module supprimé.');
        }

        return $this->redirectToRoute('admin_module_list');
    }
}
