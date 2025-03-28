<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface; // <-- Ajoutez cette ligne
use Symfony\Component\HttpFoundation\Request; // <-- Cette ligne est cruciale
use App\Entity\Planning;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\PlanningType; // Assurez-vous d'importer votre form type



final class PlanningController extends AbstractController
{
    #[Route('/planning', name: 'app_planning')]
    public function index(): Response
    {
        return $this->render('planning/index.html.twig', [
            'controller_name' => 'PlanningController',
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
        return $this->redirectToRoute('app_planning_index'); // À adapter
    }

}
