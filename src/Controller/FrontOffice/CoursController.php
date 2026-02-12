<?php

namespace App\Controller\FrontOffice;

use App\Entity\Cours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cours')]
class CoursController extends AbstractController
{
    #[Route('/{id}', name: 'app_front_office_cours_show', methods: ['GET'])]
    public function show(Cours $cours): Response
    {
        return $this->render('FrontOffice/cours/show.html.twig', [
            'cours' => $cours,
        ]);
    }
}
