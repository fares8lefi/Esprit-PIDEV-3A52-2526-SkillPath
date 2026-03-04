<?php

namespace App\Controller\Admin;

use App\Entity\Module;
use App\Form\ModuleType;
use App\Repository\ModuleRepository;
use App\Service\ModuleManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/module', name: 'admin_module_')]
class ModuleController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        ModuleRepository $moduleRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search', '');
        
        $qb = $moduleRepository->qbSearch($search);

        $modules = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('BackOffice/module/index.html.twig', [
            'modules' => $modules,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ModuleManager $moduleManager
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $module = new Module($user);
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$moduleManager->isValidTitle($module->getTitle())) {
                $this->addFlash('error', 'Le titre du module doit comporter au moins 2 caractères.');
                return $this->render('BackOffice/module/new.html.twig', [
                    'form' => $form->createView(),
                    'module' => $module,
                ]);
            }

            if (!$moduleManager->isValidDescription($module->getDescription() ?? '')) {
                $this->addFlash('error', 'La description du module doit comporter entre 10 et 500 caractères.');
                return $this->render('BackOffice/module/new.html.twig', [
                    'form' => $form->createView(),
                    'module' => $module,
                ]);
            }

            $em->persist($module);
            $em->flush();

            $this->addFlash('success', 'Module created successfully.');
            return $this->redirectToRoute('admin_module_index');
        }

        return $this->render('BackOffice/module/new.html.twig', [
            'form' => $form->createView(),
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
        EntityManagerInterface $em,
        ModuleManager $moduleManager
    ): Response {
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$moduleManager->isValidTitle($module->getTitle())) {
                $this->addFlash('error', 'Le titre du module doit comporter au moins 2 caractères.');
                return $this->render('BackOffice/module/edit.html.twig', [
                    'form' => $form->createView(),
                    'module' => $module,
                ]);
            }

            if (!$moduleManager->isValidDescription($module->getDescription() ?? '')) {
                $this->addFlash('error', 'La description du module doit comporter entre 10 et 500 caractères.');
                return $this->render('BackOffice/module/edit.html.twig', [
                    'form' => $form->createView(),
                    'module' => $module,
                ]);
            }

            $em->flush();

            $this->addFlash('success', 'Module updated successfully.');
            return $this->redirectToRoute('admin_module_index');
        }

        return $this->render('BackOffice/module/edit.html.twig', [
            'form' => $form->createView(),
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
            $this->addFlash('success', 'Module deleted.');
        }

        return $this->redirectToRoute('admin_module_index');
    }
}

