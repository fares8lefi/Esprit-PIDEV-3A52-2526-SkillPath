<?php

namespace App\Controller\BackOffice;

use App\Repository\ResultatRepository;
use App\Entity\Resultat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/resultats')]
class ResultatController extends AbstractController
{
    #[Route('/', name: 'app_back_office_resultat_index', methods: ['GET'])]
    public function index(ResultatRepository $resultatRepository): Response
    {
        return $this->render('BackOffice/resultat/index.html.twig', [
            'resultats' => $resultatRepository->findBy([], ['datePassage' => 'DESC']),
        ]);
    }

    #[Route('/{id}', name: 'app_back_office_resultat_delete', methods: ['POST'])]
    public function delete(Request $request, Resultat $resultat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$resultat->getId(), $request->request->get('_token'))) {
            $entityManager->remove($resultat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_back_office_resultat_index', [], Response::HTTP_SEE_OTHER);
    }
}
