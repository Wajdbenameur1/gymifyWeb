<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\ObjectifCours;
use App\Entity\Cours;
use App\Repository\PlanningRepository;
use App\Repository\ActivityRepository;
use App\Repository\SalleRepository;
use App\Repository\CoursRepository;



final class CoursController extends AbstractController
{
  #[Route('/cours/{idPlanning}', name: 'app_cours')]
    public function index(Request $request, CoursRepository $coursRepository, $idPlanning = null): Response
    {
        if ($idPlanning) {
            // Récupérer les cours associés à ce planning
            $cours = $coursRepository->findBy(['planning' => $idPlanning]);
        } else {
            // Tous les cours si aucun filtre
            $cours = $coursRepository->findAll();
        }

        return $this->render('cours/index.html.twig', [
            'page_title' => 'Liste des cours',
            'cours' => $cours,
            'idPlanning' => $idPlanning
        ]);
    }

    
    #[Route('/cours/new/{idPlanning}', name: 'app_cours_new')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager, $idPlanning = null,
        SalleRepository $salleRepository,
        PlanningRepository $planningRepository,
        ActivityRepository $activityRepository
    ): Response {
      $planning = $planningRepository->find($idPlanning);

        return $this->render('cours/new.html.twig', [
            'page_title' => 'Add New Courses',
            'objectifs' => ObjectifCours::cases(),
            'activites' => $activityRepository->findAll(),
            'salles' => $salleRepository->findAll(),
            'idPlanning' => $idPlanning,
            'planning_start' => $planning->getDateDebut()->format('Y-m-d'),
            'planning_end' => $planning->getDateFin()->format('Y-m-d'),
            'planning' => $planning, // Ajoutez cette ligne

            
        ]);
    }
    #[Route('/cours/create/{idPlanning}', name: 'app_cours_create', methods: ['POST'])]
    public function create(
    Request $request,
    EntityManagerInterface $entityManager,
    PlanningRepository $planningRepo,
    ActivityRepository $activiteRepo,
    SalleRepository $salleRepo,
    $idPlanning = null): Response {
    // 1. Vérifier l'utilisateur connecté
    /*$user = $this->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException();
    }*/

    // 2. Récupérer le planning
    $planning = $planningRepo->find($idPlanning);
    if (!$planning) {
        throw $this->createNotFoundException('Planning non trouvé');
    }

    // 3. Créer le cours
    $cours = new Cours();
    $cours->setTitle($request->request->get('title'));
    $cours->setDescription($request->request->get('description'));
    $cours->setObjectif(ObjectifCours::from($request->request->get('objectif')));
    $cours->setDateDebut(new \DateTime($request->request->get('dateDebut')));
    $cours->setHeurDebut(new \DateTime($request->request->get('heurDebut')));
    $cours->setHeurFin(new \DateTime($request->request->get('heurFin')));
    
    // 4. Associer les relations
    $cours->setActivité($activiteRepo->find($request->request->get('activite')));
    $cours->setSalle($salleRepo->find($request->request->get('salle')));
    //$cours->setEntraineur($user);
    $cours->setPlanning($planning);

    // 5. Enregistrer
    $entityManager->persist($cours);
    $entityManager->flush();

    $this->addFlash('success', 'Cours créé avec succès!');
    return $this->redirectToRoute('app_cours_new',[
      'idPlanning' => $idPlanning]);
}
}
