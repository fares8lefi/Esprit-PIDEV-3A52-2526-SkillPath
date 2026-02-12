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

#[Route('/reclamation')]
#[IsGranted('ROLE_USER')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        return $this->render('FrontOffice/reclamation/index.html.twig', [
            'reclamations' => $reclamationRepository->findBy(['user' => $this->getUser()]),
        ]);
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $reclamation = new Reclamation();
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
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $reclamation->setPieceJointe($newFilename);
            }

            $reclamation->setUser($this->getUser());
            $reclamation->setStatut('Pending');
            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('FrontOffice/reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($reclamation->getUser() !== $this->getUser()) {
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
            'response_form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($reclamation->getUser() !== $this->getUser()) {
             throw $this->createAccessDeniedException('You cannot edit this reclamation.');
        }

        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }
}
