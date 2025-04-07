<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface; // <-- Ajoutez cette ligne
use Symfony\Component\HttpFoundation\Request; // <-- Cette ligne est cruciale
use App\Entity\Planning;
use App\Repository\PlanningRepository; 

use Symfony\Component\Routing\Attribute\Route;
use App\Form\PlanningType;// Assurez-vous d'importer votre form type



final class PlanningController extends AbstractController
{
    #[Route('/planning', name: 'app_planning')]
    public function index(EntityManagerInterface $entityManager): Response
{
    $plannings = $entityManager->getRepository(Planning::class)->findAll();
    
    return $this->render('planning/index.html.twig', [
        'plannings' => $plannings,
        'page_title' => 'Liste des plannings'
    ]);
}
    #[Route('/planning/new', name: 'app_planning_new')]
    public function new(Request $request): Response
{
    
    
    return $this->render('planning/new.html.twig', [
        'page_title' => 'Add New Planning',
    ]);
}
#[Route('/planning/create', name: 'app_planning_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        //Security $security
    ): Response {
        // 1. Vérifier le token CSRF
        $submittedToken = $request->request->get('token');
        if (!$this->isCsrfTokenValid('planning_form', $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('votre_route_de_retour');
        }

        // 2. Récupérer l'utilisateur connecté (entraîneur)
        /** @var User $user */
        //$user = $security->getUser();
        $planning = new Planning();
        $planning->setTitle($request->request->get('title'));
        $planning->setDescription($request->request->get('description'));
        $planning->setDateDebut(new \DateTime($request->request->get('dateDebut')));
        $planning->setDateFin(new \DateTime($request->request->get('dateFin')));
        //$planning->setEntraineur($user);

        // 4. Enregistrer en base de données
        $entityManager->persist($planning);
        $entityManager->flush();

        // 5. Redirection avec message de succès
        $this->addFlash('success', 'Planning créé avec succès!');
        return $this->redirectToRoute('app_planning'); // À adapter
    }
    #[Route('/planning/edit/{id}', name: 'app_planning_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, PlanningRepository $repository): Response
    {
        $planning = $repository->find($id);
        
        if (!$planning) {
            $this->addFlash('error', 'Planning not found');
            return $this->redirectToRoute('app_planning_index');
        }
    
        return $this->render('planning/edit.html.twig', [
            'page_title' => 'Edit Planning',
            'planning' => $planning,
        ]);
    }

#[Route('/planning/{id}', name: 'app_planning_delete', methods: ['POST'])]
public function delete(Request $request, Planning $planning, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete'.$planning->getId(), $request->request->get('_token'))) {
        $entityManager->remove($planning);
        $entityManager->flush();
        $this->addFlash('success', 'Planning supprimé avec succès');
    }

    return $this->redirectToRoute('app_planning');
}
#[Route('/planning/{id}/update', name: 'app_planning_update', methods: ['POST'])]
public function update(Request $request, Planning $planning, EntityManagerInterface $entityManager): Response
{
    // Validation du token CSRF
    if (!$this->isCsrfTokenValid('update_planning_'.$planning->getId(), $request->request->get('_token'))) {
        $this->addFlash('error', 'Token CSRF invalide');
        return $this->redirectToRoute('app_planning_edit', ['id' => $planning->getId()]);
    }

    // Mise à jour des données
    $planning->setTitle($request->request->get('title'));
    $planning->setDescription($request->request->get('description'));
    $planning->setDateDebut(new \DateTime($request->request->get('dateDebut')));
    $planning->setDateFin(new \DateTime($request->request->get('dateFin')));

    $entityManager->flush();

    $this->addFlash('success', 'Planning mis à jour avec succès');
    return $this->redirectToRoute('app_planning');
}


    
    

}
