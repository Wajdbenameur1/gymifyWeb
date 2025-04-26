<?php

namespace App\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Abonnement;
use App\Entity\Paiement;
use App\Form\AbonnementType;
use App\Repository\AbonnementRepository;
use App\Repository\ActivityRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Knp\Component\Pager\PaginatorInterface;
#[Route('/responsable/abonnement')]
#[IsGranted('ROLE_RESPONSABLE')]
class AbonnementController extends AbstractController
{
    #[Route('/', name: 'responsable_abonnement_index', methods: ['GET'])]
    public function index(
        AbonnementRepository $abonnementRepository,
        Request $request,
        ActivityRepository $activityRepository,
        PaginatorInterface $paginator
    ): Response {
        $salle = $this->getUser()->getSalle();
    
        // Récupérer les paramètres de filtrage
        $typeFilter = $request->query->get('type');
        $activityFilter = $request->query->get('activity');
    
        // Convertir la valeur si nécessaire (ex: 'Mensuel' -> 'mois')
        $typeFilterValue = match($typeFilter) {
            'Mensuel' => 'mois',
            'Trimestriel' => 'trimestre',
            'Annuel' => 'année',
            default => $typeFilter
        };
    
        // Créer la requête avec les filtres
        $query = $abonnementRepository->createQueryByFilters(
            $salle,
            $typeFilterValue,
            $activityFilter
        );
    
        // Paginer les résultats avec 5 éléments par page
        $pagination = $paginator->paginate(
            $query, // Utiliser $query au lieu de $queryBuilder
            $request->query->getInt('page', 1),
            5 // Limite à 5 abonnements par page
        );
    
        // Récupérer toutes les activités pour le filtre
        $activities = $activityRepository->findAll(); // Correction de $activiteRepository en $activityRepository
    
        return $this->render('abonnement/index.html.twig', [
            'abonnements' => $pagination,
            'activities' => $activities,
            'current_type_filter' => $typeFilter, // Correction de la faute de frappe
            'current_activity_filter' => $activityFilter,
        ]);
    }

    #[Route('/new', name: 'responsable_abonnement_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ActivityRepository $activityRepository
    ): Response {
        $abonnement = new Abonnement();
        $salle = $this->getUser()->getSalle();
        $abonnement->setSalle($salle); // Associe la salle de l'utilisateur connecté
        
        $form = $this->createForm(AbonnementType::class, $abonnement, [
            'activites' => $activityRepository->findAll()
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($abonnement);
            $em->flush();
            $this->addFlash('success', 'Abonnement créé avec succès');
            return $this->redirectToRoute('responsable_abonnement_index');
        }

        return $this->render('abonnement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'responsable_abonnement_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Abonnement $abonnement,
        EntityManagerInterface $em,
        ActivityRepository $activityRepository
    ): Response {
        $salle = $this->getUser()->getSalle();
        if ($abonnement->getSalle() !== $salle) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AbonnementType::class, $abonnement, [
            'activites' => $activityRepository->findAll()
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Abonnement mis à jour avec succès');
            return $this->redirectToRoute('responsable_abonnement_index');
        }

        return $this->render('abonnement/edit.html.twig', [
            'form' => $form->createView(),
            'abonnement' => $abonnement,
        ]);
    }

    #[Route('/{id}/delete', name: 'responsable_abonnement_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Abonnement $abonnement,
        EntityManagerInterface $em
    ): Response {
        $salle = $this->getUser()->getSalle();
        if ($abonnement->getSalle() !== $salle) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$abonnement->getId(), $request->request->get('_token'))) {
            $em->remove($abonnement);
            $em->flush();
            $this->addFlash('success', 'Abonnement supprimé avec succès');
        }

        return $this->redirectToRoute('responsable_abonnement_index');
    }

    #[Route('/export-pdf', name: 'responsable_abonnement_export_pdf', methods: ['GET'])]
    public function exportPdf(
        Request $request,
        AbonnementRepository $abonnementRepository,
        ActivityRepository $activityRepository
    ): Response {
        $user = $this->getUser();
        $salle = $user->getSalle();
        
        if (!$salle) {
            $this->addFlash('error', 'Aucune salle associée à votre compte');
            return $this->redirectToRoute('responsable_abonnement_index');
        }
    
        $typeFilter = $request->query->get('type');
        $activityFilter = $request->query->get('activity');
    
        $typeFilterValue = match($typeFilter) {
            'Mensuel' => 'mois',
            'Trimestriel' => 'trimestre',
            'Annuel' => 'année',
            default => $typeFilter
        };
    
        $query = $abonnementRepository->createQueryByFilters(
            $salle,
            $typeFilterValue,
            $activityFilter
        );
        $abonnements = $query->getQuery()->getResult();
    
        $activityName = null;
        if ($activityFilter) {
            $activity = $activityRepository->find($activityFilter);
            $activityName = $activity ? $activity->getNom() : null;
        }
    
        $pdfOptions = new Options();
        $pdfOptions->set([
            'defaultFont' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'tempDir' => sys_get_temp_dir()
        ]);
    
        $dompdf = new Dompdf($pdfOptions);
    
        $html = $this->renderView('abonnement/export_pdf.html.twig', [
            'abonnements' => $abonnements,
            'salle' => $salle,
            'filters' => [
                'type' => $typeFilter,
                'activity' => $activityName,
            ],
            'export_date' => new \DateTime(),
            'user' => $user
        ]);
    
        try {
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
    
            $filename = sprintf('abonnements_%s_%s.pdf', 
                $salle->getNom(),
                (new \DateTime())->format('Y-m-d')
            );
    
            return new Response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename)
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la génération du PDF');
            return $this->redirectToRoute('responsable_abonnement_index');
        }
    }
}