<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin/location', name: 'admin_location_')]
class LocationController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(LocationRepository $locationRepository): Response
    {
        $locations = $locationRepository->findBy([], ['name' => 'ASC']);

        return $this->render('BackOffice/location/list.html.twig', [
            'locations' => $locations,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $original = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = $slugger->slug($original);
                $newName = $safeName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('locations_upload_dir'), $newName);
                    $location->setImage($newName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Upload échoué.');
                }
            }

            $entityManager->persist($location);
            $entityManager->flush();

            $this->addFlash('success', 'Le lieu a été créé avec succès.');
            return $this->redirectToRoute('admin_location_list');
        }

        return $this->render('BackOffice/location/new.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Location $location): Response
    {
        return $this->render('BackOffice/location/show.html.twig', [
            'location' => $location,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Location $location, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $original = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = $slugger->slug($original);
                $newName = $safeName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('locations_upload_dir'), $newName);
                    $location->setImage($newName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Upload échoué.');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Le lieu a été modifié avec succès.');
            return $this->redirectToRoute('admin_location_list');
        }

        return $this->render('BackOffice/location/edit.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Location $location, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $location->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($location);
                $entityManager->flush();
                $this->addFlash('success', 'Le lieu a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_location_list');
    }
}
