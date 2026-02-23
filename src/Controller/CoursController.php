<?php 
 namespace App\Controller;

use App\Entity\Cours;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Service\NotificationService;

#[Route('/admin/cours', name: 'admin_cours_')]
class CoursController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(
        Request $request,
        CoursRepository $coursRepository,
        PaginatorInterface $paginator
    ): Response {
        // Récupérer les filtres
        $search = $request->query->get('search', '');
        $typeFilter = $request->query->get('type', '');

        // Créer la requête de base
        $queryBuilder = $coursRepository->createQueryBuilder('c');

        // Appliquer les filtres
        if ($search) {
            $queryBuilder
                ->andWhere('c.titre LIKE :search OR c.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Module and Type filters removed from here as they belong to Module entity

        // Trier par ID décroissant
        $queryBuilder->orderBy('c.id', 'DESC');

        // Paginer les résultats
        $cours = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10 // Nombre d'éléments par page
        );

        return $this->render('BackOffice/cours/list.html.twig', [
            'cours' => $cours,
            'modules' => [], // Empty array to satisfy template loop if not removed
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, NotificationService $notificationService): Response
    {
        $cours = new Cours();
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // Image Upload
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $original = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = $slugger->slug($original);
                $newName  = $safeName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    // Assuming 'modules_upload_dir' parameter exists as per previous ModuleController code
                    $imageFile->move($this->getParameter('modules_upload_dir'), $newName);
                    $cours->setImage($newName);
                } catch (FileException $e) {
                    $this->addFlash('error', "Upload échoué.");
                }
            }

            $entityManager->persist($cours);
            $entityManager->flush();

            // Notify students
            $notificationService->notifyNewContent('cours', $cours->getTitre());

            $this->addFlash('success', 'Le contenu a été ajouté avec succès.');

            return $this->redirectToRoute('admin_cours_list');
        }

        return $this->render('BackOffice/cours/new.html.twig', [
            'cours' => $cours,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Cours $cours): Response
    {
        return $this->render('BackOffice/cours/show.html.twig', [
            'cours' => $cours,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Cours $cours, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // Image Upload
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $original = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = $slugger->slug($original);
                $newName  = $safeName . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                     $imageFile->move($this->getParameter('modules_upload_dir'), $newName);
                     $cours->setImage($newName);
                } catch (FileException $e) {
                    $this->addFlash('error', "Upload échoué.");
                }
            }

            $cours->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Le contenu a été modifié avec succès.');

            return $this->redirectToRoute('admin_cours_list');
        }

        return $this->render('BackOffice/cours/edit.html.twig', [
            'cours' => $cours,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Cours $cours, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cours->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($cours);
                $entityManager->flush();
                $this->addFlash('success', 'Le contenu a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_cours_list');
    }
}
