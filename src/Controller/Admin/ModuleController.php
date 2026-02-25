<?php

namespace App\Controller\Admin;

use App\Entity\Module;
use App\Form\ModuleType;
use App\Repository\ModuleRepository;
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
        EntityManagerInterface $em
    ): Response {
        $module = new Module();
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documentFile = $form->get('documentFile')->getData();
            if ($documentFile) {
                $newFilename = uniqid() . '-' . $documentFile->getClientOriginalName();
                try {
                    $documentFile->move(
                        $this->getParameter('modules_upload_dir'),
                        $newFilename
                    );
                    $module->setDocument($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du document.');
                }
            }

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newImageName = uniqid() . '-' . $imageFile->getClientOriginalName();
                try {
                    $imageFile->move(
                        $this->getParameter('modules_upload_dir'),
                        $newImageName
                    );
                    $module->setImage($newImageName);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
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
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documentFile = $form->get('documentFile')->getData();
            if ($documentFile) {
                $newFilename = uniqid() . '-' . $documentFile->getClientOriginalName();
                try {
                    $documentFile->move(
                        $this->getParameter('modules_upload_dir'),
                        $newFilename
                    );
                    
                    // Optionnel : Supprimer l'ancien fichier ici
                    
                    $module->setDocument($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du document.');
                }
            }

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newImageName = uniqid() . '-' . $imageFile->getClientOriginalName();
                try {
                    $imageFile->move(
                        $this->getParameter('modules_upload_dir'),
                        $newImageName
                    );
                    $module->setImage($newImageName);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
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

