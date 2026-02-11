<?php 
 namespace App\Controller;

use App\Entity\Cours;
use App\Entity\Module;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin/cours', name: 'admin_cours_')]
class CoursController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(
        Request $request,
        CoursRepository $coursRepository,
        moduleRepository $moduleRepository,
        PaginatorInterface $paginator
    ): Response {
        // Récupérer les filtres
        $search = $request->query->get('search', '');
        $moduleFilter = $request->query->get('module', '');
        $typeFilter = $request->query->get('type', '');

        // Créer la requête de base
        $queryBuilder = $coursRepository->createQueryBuilder('c')
            ->leftJoin('c.module', 'm')
            ->addSelect('m');

        // Appliquer les filtres
        if ($search) {
            $queryBuilder
                ->andWhere('c.titre LIKE :search OR c.contenu LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($moduleFilter) {
            $queryBuilder
                ->andWhere('c.module = :module')
                ->setParameter('module', $moduleFilter);
        }

        if ($typeFilter) {
            $queryBuilder
                ->andWhere('c.type = :type')
                ->setParameter('type', $typeFilter);
        }

        // Trier par ID décroissant
        $queryBuilder->orderBy('c.id', 'DESC');

        // Paginer les résultats
        $cours = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10 // Nombre d'éléments par page
        );

        // Récupérer tous les modules pour le filtre
        $modules = $moduleRepository->findAll();

        return $this->render('BackOffice/cours/list.html.twig', [
            'cours' => $cours,
            'modules' => $modules,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cours = new Cours();
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cours);
            $entityManager->flush();

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
    public function edit(Request $request, Cours $cours, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CoursType::class, $cours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
