<?php

namespace App\Controller;

use App\Entity\Module;
use App\Repository\ModuleRepository;
use App\Form\ModuleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModuleController extends AbstractController
{
    // LISTE
    #[Route('/modules', name: 'module_list')]
    public function list(ModuleRepository $repo): Response
    {
        return $this->render('BackOffice/module/list.html.twig', [
            'modules' => $repo->findAll()
        ]);
    }

    // AJOUT
    #[Route('/modules/new', name: 'module_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $module = new Module();
        $form = $this->createForm(ModuleType::class, $module);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($module);
            $em->flush();
            $this->addFlash('success', 'Le module a été créé avec succès.');
            return $this->redirectToRoute('module_list');
        }

        return $this->render('BackOffice/module/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // DELETE
    #[Route('/modules/delete/{id}', name: 'module_delete')]
    public function delete(Module $module, EntityManagerInterface $em): Response
    {
        $em->remove($module);
        $em->flush();
        $this->addFlash('success', 'Le module a été supprimé.');
        return $this->redirectToRoute('module_list');
    }

    // EDIT
    #[Route('/modules/edit/{id}', name: 'module_edit')]
    public function edit(Request $request, Module $module, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ModuleType::class, $module);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Le module a été mis à jour.');
            return $this->redirectToRoute('module_list');
        }

        return $this->render('BackOffice/module/edit.html.twig', [
            'form' => $form->createView(),
            'module' => $module
        ]);
    }
}
