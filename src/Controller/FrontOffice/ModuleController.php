<?php

namespace App\Controller\FrontOffice;

use App\Entity\Module;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/module')]
class ModuleController extends AbstractController
{
    #[Route('/{id}', name: 'app_front_office_module_show', methods: ['GET'])]
    public function show(Module $module): Response
    {
        return $this->render('FrontOffice/module/show.html.twig', [
            'module' => $module,
        ]);
    }
}
