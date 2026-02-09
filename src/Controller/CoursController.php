<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/cours')]
class CoursController extends AbstractController
{
    // =========================
    // LISTE DES COURS
    // =========================
    #[Route('/', name: 'cours_list')]
    public function list(CoursRepository $repo): Response
    {
        return $this->render('BackOffice/cours/list.html.twig', [
            'cours' => $repo->findAll()
        ]);
    }

    // =========================
    // AJOUTER COURS (test auto)
    // =========================
    #[Route('/new', name: 'cours_new')]
    public function new(EntityManagerInterface $em): Response
    {
        $cours = new Cours();
        $cours->setTitre('Nouveau cours');
        $cours->setContenu('Contenu du cours');
        $cours->setType('PDF');

        $em->persist($cours);
        $em->flush();

        return $this->redirectToRoute('cours_list');
    }

    // =========================
    // SUPPRIMER
    // =========================
    #[Route('/delete/{id}', name: 'cours_delete')]
    public function delete($id, CoursRepository $repo, EntityManagerInterface $em): Response
    {
        $cours = $repo->find($id);

        if (!$cours) {
            return new Response('Cours introuvable');
        }

        $em->remove($cours);
        $em->flush();

        return $this->redirectToRoute('cours_list');
    }

    // =========================
    // MODIFIER (test simple)
    // =========================
    #[Route('/edit/{id}', name: 'cours_edit')]
    public function edit($id, CoursRepository $repo, EntityManagerInterface $em): Response
    {
        $cours = $repo->find($id);

        if (!$cours) {
            return new Response('Cours introuvable');
        }

        $cours->setTitre('Titre modifié');
        $em->flush();

        return $this->redirectToRoute('cours_list');
    }
}
