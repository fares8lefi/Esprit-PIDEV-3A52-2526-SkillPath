<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReclamationType;
use App\Form\ReponseType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/reclamation')]
#[IsGranted('ROLE_USER')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'desc');

        return $this->render('FrontOffice/reclamation/index.html.twig', [
            'reclamations' => $reclamationRepository->findBySearchAndSort($search, $sort, $direction, $this->getUser()),
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, \App\Service\OllamaService $ollamaService): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $description = $reclamation->getDescription();
            
            // AI Analysis
            $analysis = $ollamaService->analyzeReclamation($description);
            
            if ($analysis['has_bad_words']) {
                $this->addFlash('error', 'Votre réclamation contient des mots inappropriés et a été refusée.');
                $form->get('description')->addError(new \Symfony\Component\Form\FormError('Contenu inapproprié détecté. Veuillez reformuler.'));
                return $this->render('FrontOffice/reclamation/new.html.twig', [
                    'reclamation' => $reclamation,
                    'form' => $form->createView(),
                ]);
            }

            $statut = 'Pending';
            if ($analysis['is_aggressive']) {
                $statut = 'Urgent';
                $reclamation->setDescription($analysis['clean_description'] ?? $description);
            }

            /** @var UploadedFile $attachmentFile */
            $attachmentFile = $form->get('pieceJointe')->getData();

            if ($attachmentFile) {
                $originalFilename = pathinfo($attachmentFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$attachmentFile->guessExtension();

                try {
                    $attachmentFile->move(
                        $this->getParameter('reclamations_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception
                }

                $reclamation->setPieceJointe($newFilename);
            }

            $reclamation->setUser($this->getUser());
            $reclamation->setStatut($statut);
            $entityManager->persist($reclamation);
            $entityManager->flush();

            if ($statut === 'Urgent') {
                $this->addFlash('warning', 'Votre réclamation a été marquée comme URGENTE en raison de votre ton passionné. Nous la traiterons en priorité.');
            }

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('FrontOffice/reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET', 'POST'])]
    public function show(Reclamation $reclamation, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Allow access if user is owner OR has ROLE_ADMIN
        $user = $this->getUser();
        if ($reclamation->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
             throw $this->createAccessDeniedException('You cannot view this reclamation.');
        }

        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setReclamation($reclamation);
            $reponse->setUser($this->getUser());
            $entityManager->persist($reponse);
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_show', ['id' => $reclamation->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('FrontOffice/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
            'response_form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Only owner can edit
        if ($reclamation->getUser() !== $this->getUser()) {
             throw $this->createAccessDeniedException('You cannot edit this reclamation.');
        }

        if ($reclamation->getStatut() !== 'Pending') {
            $this->addFlash('error', 'Vous ne pouvez plus modifier cette réclamation car elle n\'est plus en attente.');
            return $this->redirectToRoute('app_reclamation_show', ['id' => $reclamation->getId()]);
        }

        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $attachmentFile */
            $attachmentFile = $form->get('pieceJointe')->getData();

            if ($attachmentFile) {
                $originalFilename = pathinfo($attachmentFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$attachmentFile->guessExtension();

                try {
                    $attachmentFile->move(
                        $this->getParameter('reclamations_directory'),
                        $newFilename
                    );
                    $reclamation->setPieceJointe($newFilename);
                } catch (FileException $e) {
                    // ... handle exception
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('FrontOffice/reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        // Owner OR Admin can delete
        if ($reclamation->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
             throw $this->createAccessDeniedException('You cannot delete this reclamation.');
        }

        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
}
