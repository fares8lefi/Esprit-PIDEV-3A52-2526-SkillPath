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

#[Route('/admin/modules', name: 'admin_module_')]
class ModuleController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(
        Request $request,
        ModuleRepository $moduleRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search', '');
        $level  = $request->query->get('level', '');

        $qb = $moduleRepository->qbSearch($search, $level);

        $modules = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('BackOffice/module/list.html.twig', [
            'modules' => $modules,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $module = new Module();
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $original = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = $slugger->slug($original);
                $newName  = $safeName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('modules_upload_dir'), $newName);
                    $module->setImage($newName);
                } catch (FileException $e) {
                    $this->addFlash('error', "Upload échoué.");
                }
            }

            $em->persist($module);
            $em->flush();

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
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $original = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = $slugger->slug($original);
                $newName  = $safeName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('modules_upload_dir'), $newName);
                    $module->setImage($newName);
                } catch (FileException $e) {
                    $this->addFlash('error', "Upload échoué.");
                }
            }

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
