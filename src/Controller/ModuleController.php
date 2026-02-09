<?php

namespace App\Controller;

use App\Entity\Module;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function new(EntityManagerInterface $em): Response
    {
        $module = new Module();
        $module->setName('Nouveau module');
        $module->setDescription('Description exemple');
        $module->setDateCreation(new \DateTime());
        $module->setLevel('Débutant');
        $module->setImage('image.png');

        $em->persist($module);
        $em->flush();

        return $this->redirectToRoute('module_list');
    }

    // DELETE
    #[Route('/modules/delete/{id}', name: 'module_delete')]
    public function delete($id, ModuleRepository $repo, EntityManagerInterface $em): Response
    {
        $module = $repo->find($id);

        if (!$module) {
            return new Response('Module introuvable');
        }

        $em->remove($module);
        $em->flush();

        return $this->redirectToRoute('module_list');
    }

    // EDIT
    #[Route('/modules/edit/{id}', name: 'module_edit')]
    public function edit($id, ModuleRepository $repo, EntityManagerInterface $em): Response
    {
        $module = $repo->find($id);

        if (!$module) {
            return new Response('Module introuvable');
        }

        $module->setName('Nom modifié');
        $em->flush();

        return $this->redirectToRoute('module_list');
    }
}
