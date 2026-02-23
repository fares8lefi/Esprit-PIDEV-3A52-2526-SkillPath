<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReclamationStatusType;
use App\Form\ReponseType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reclamation')]
#[IsGranted('ROLE_ADMIN')]
class AdminReclamationController extends AbstractController
{
    #[Route('/', name: 'app_admin_reclamation_index', methods: ['GET'])]
    public function index(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'desc');

        return $this->render('BackOffice/reclamation/index.html.twig', [
            'reclamations' => $reclamationRepository->findBySearchAndSort($search, $sort, $direction),
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_reclamation_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $reponse = new Reponse();
        $responseForm = $this->createForm(ReponseType::class, $reponse);
        $responseForm->handleRequest($request);

        if ($responseForm->isSubmitted() && $responseForm->isValid()) {
            $reponse->setReclamation($reclamation);
            $reponse->setUser($this->getUser());
            $entityManager->persist($reponse);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_reclamation_show', ['id' => $reclamation->getId()], Response::HTTP_SEE_OTHER);
        }

        $statusForm = $this->createForm(ReclamationStatusType::class, $reclamation);
        $statusForm->handleRequest($request);

        if ($statusForm->isSubmitted() && $statusForm->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_reclamation_show', ['id' => $reclamation->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('BackOffice/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
            'response_form' => $responseForm,
            'status_form' => $statusForm,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
}
