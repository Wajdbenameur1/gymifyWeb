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
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;





final class CoursController extends AbstractController
{
  #[Route('/cours/{idPlanning}', name: 'app_cours')]
public function index(Request $request, CoursRepository $coursRepository, 
  PlanningRepository $planningRepository, 
  $idPlanning = null): Response
{
    $planning = $planningRepository->find($idPlanning);

    if (!$planning) {
        throw $this->createNotFoundException('Planning non trouvé');
    }

    // Générer toutes les dates entre dateDebut et dateFin du planning
    $joursCalendrier = [];
    $dateDebut = $planning->getDateDebut();
    $dateFin = $planning->getDateFin();
    
    // Vérifier que les dates sont valides
    if ($dateDebut && $dateFin) {
        $currentDate = clone $dateDebut;
        while ($currentDate <= $dateFin) {
            $joursCalendrier[] = clone $currentDate;
            $currentDate->modify('+1 day');
        }
    }

    // Récupérer les cours associés à ce planning
    $cours = $coursRepository->findBy(['planning' => $planning]);

    return $this->render('cours/index.html.twig', [
        'page_title' => 'Liste des cours',
        'cours' => $cours,
        'planning' => $planning,
        'joursCalendrier' => $joursCalendrier,
        'idPlanning' => $idPlanning
    ]);
}

    
    #[Route('/cours/new/{idPlanning}', name: 'app_cours_new')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager, $idPlanning = null,
        SalleRepository $salleRepository,
        PlanningRepository $planningRepository,

        ActivityRepository $activityRepository,


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



    Security $security,
    ValidatorInterface $validator,
    $idPlanning = null): Response {
    // 1. Vérifier l'utilisateur connecté
    

    // 2. Récupérer le planning
    $planning = $planningRepo->find($idPlanning);
    if (!$planning) {
        throw $this->createNotFoundException('Planning non trouvé');
    }

    // 3. Créer le cours
    /** @var User $user */
    $user = $security->getUser();



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

    $cours->setEntaineur($user);
    $cours->setPlanning($planning);
    $errors = $validator->validate($activity);
         if (count($errors) > 0) {
        foreach ($errors as $error) {
            $this->addFlash('error', $error->getMessage());
        }
        return $this->redirectToRoute('app_cours_new');
        }


    // 5. Enregistrer
    $entityManager->persist($cours);
    $entityManager->flush();

    $this->addFlash('success', 'Cours créé avec succès!');
    return $this->redirectToRoute('app_cours_new',[
      'idPlanning' => $idPlanning]);
}
#[Route('/delete/{id}', name: 'app_cours_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        Cours $cour, 
        EntityManagerInterface $entityManager
    ): Response {
      if (!$cour->getPlanning()) {
        throw new \RuntimeException('Aucun planning associé à ce cours');
    }
        $idPlanning = $cour->getPlanning()->getId();
        if ($this->isCsrfTokenValid('delete'.$cour->getId(), $request->request->get('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();
            $this->addFlash('success', 'Le cours a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_cours', ['idPlanning' => $idPlanning]);
    }

#[Route('/cours/edit/{id}', name: 'app_cours_edit')]
public function edit(
    Request $request,
    Cours $cour,
    ActivityRepository $activiteRepository,
    SalleRepository $salleRepository,
    EntityManagerInterface $entityManager): Response {
    // Vérification du planning
    $planning = $cour->getPlanning();
    
    if (!$planning) {
        $this->addFlash('error', 'Ce cours n\'est associé à aucun planning');
        return $this->redirectToRoute('app_cours_index');
    }

        return $this->render('cours/edit.html.twig', [
            'page_title' => 'Modifier le cours : ' . $cour->getTitle(),
            'cour' => $cour,
            'objectifs' => ObjectifCours::cases(), // Assurez-vous que c'est ObjectifEnum et non ObjectifCours
            'activites' => $activiteRepository->findAll(),
            'salles' => $salleRepository->findAll(),
            'idPlanning' => $planning->getId(),
            'planning_start' => $planning->getDateDebut() ? $planning->getDateDebut()->format('Y-m-d') : null,
            'planning_end' => $planning->getDateFin() ? $planning->getDateFin()->format('Y-m-d') : null,
            'planning' => $planning,
        ]);
    
}
#[Route('/cours/update/{id}', name: 'app_cours_Update', methods: ['POST'])]
public function update(
    Request $request, 
    Cours $cours, 
    EntityManagerInterface $entityManager,
    ActivityRepository $activiteRepo,

    SalleRepository $salleRepo,
    ValidatorInterface $validator,

): Response {
    // Récupérer les données du formulaire
    $title = $request->request->get('title');
    $description = $request->request->get('description');
    $objectif = ObjectifCours::from($request->request->get('objectif')); // Conversion correcte de l'enum
    $dateDebut = new \DateTime($request->request->get('dateDebut'));
    $heurDebut = \DateTime::createFromFormat('H:i', $request->request->get('heurDebut'));
    $heurFin = \DateTime::createFromFormat('H:i', $request->request->get('heurFin'));
    
    // Mettre à jour l'entité
    $cours->setTitle($title);
    $cours->setDescription($description);
    $cours->setObjectif($objectif);
    $cours->setDateDebut($dateDebut);
    $cours->setHeurDebut($heurDebut);
    $cours->setHeurFin($heurFin);

    $errors = $validator->validate($activity);
    if (count($errors) > 0) {
   foreach ($errors as $error) {
       $this->addFlash('error', $error->getMessage());
   }
   return $this->redirectToRoute('app_cours_edit', ['id' => $cours->getId()]);
   }

    
    // Gestion des relations
    $activiteId = $request->request->get('activite');
    $salleId = $request->request->get('salle');
    
    $activite = $activiteRepo->find($activiteId);
    $salle = $salleRepo->find($salleId);
    
    if ($activite) {
        $cours->setActivité($activite);
    }
    
    if ($salle) {
        $cours->setSalle($salle);
    }
    
    try {
        // Enregistrer en base
        $entityManager->flush();
        
        // Récupérer l'ID du planning pour la redirection
        $planningId = $cours->getPlanning() ? $cours->getPlanning()->getId() : null;
        
        if ($planningId) {
            $this->addFlash('success', 'Le cours a été modifié avec succès');
            return $this->redirectToRoute('app_cours', ['idPlanning' => $planningId]);
        } else {
            throw new \Exception('Aucun planning associé à ce cours');
        }
    } catch (\Exception $e) {
        $this->addFlash('error', 'Une erreur est survenue lors de la modification du cours');
        return $this->redirectToRoute('app_cours_edit', ['id' => $cours->getId()]);
    }
}
}