<?php

namespace App\Controller;
use App\Repository\AbonnementRepository;
use App\Repository\EventsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CoursRepository;
use App\Entity\Cours;
use App\Enum\ObjectifCours;
use App\Repository\SalleRepository;
use App\Entity\Salle; // Add this for the salle details

final class SportifController extends AbstractController
{
    #[Route('/sportif', name: 'home')]
    public function sportif(SalleRepository $salleRepository): Response
    {
        $salles = $salleRepository->findAll();
        return $this->render('sportif/index.html.twig', [
            'salles' => $salles,
        ]);
    }
    #[Route('/sportif/planning', name: 'plan')]
    public function index(CoursRepository $coursRepository): Response
    {
        $cours = $coursRepository->findAll();
        $events = [];

        foreach ($cours as $cour) {
            $events[] = [
                'title' => $cour->getTitle(),
                'start' => $this->mergeDateTime($cour->getDateDebut(), $cour->getHeurDebut()),
                'end' => $this->mergeDateTime($cour->getDateDebut(), $cour->getHeurFin()),
                'color' => $this->getColorForObjectif($cour->getObjectif()),
                'description' => $cour->getDescription(),
                'extendedProps' => [
                    'activite' => $cour->getActivité() ? $cour->getActivité()->getNom() : null,
                    'salle' => $cour->getSalle() ? $cour->getSalle()->getNom() : null
                ]
            ];
        }

        return $this->render('sportif/planning.html.twig', [
            'events' => $events
        ]);
    }

    // New action for salle details
    #[Route('/sportif/salle/{id}', name: 'sportif_salle_details')]
public function salleDetails(Salle $salle, AbonnementRepository $abonnementRepository, EventsRepository $eventsRepository): Response
{
    $abonnements = $abonnementRepository->findBy(['salle' => $salle]);
    $evenements = $eventsRepository->findBy(['salle' => $salle]);
    
    return $this->render('sportif/salle_details.html.twig', [
        'salle' => $salle,
        'abonnements' => $abonnements,
        'evenements' => $evenements,
    ]);
}

    private function mergeDateTime(\DateTimeInterface $date, \DateTimeInterface $time): string
    {
        return sprintf(
            '%sT%s',
            $date->format('Y-m-d'),
            $time->format('H:i:s')
        );
    }

    private function getColorForObjectif(?ObjectifCours $objectif): string
    {
        if (null === $objectif) {
            return '#CCCCCC';
        }

        return match ($objectif) {
            ObjectifCours::PERTE_POIDS => '#FF5733',
            ObjectifCours::PRISE_DE_MASSE => '#33FF57',
            ObjectifCours::ENDURANCE => '#3357FF',
            ObjectifCours::RELAXATION => '#F033FF',
            default => '#CCCCCC',
        };
    }
    #[Route('/sportif/abonnement/souscrire/{id}', name: 'abonnement_souscrire', methods: ['GET'])]
    public function souscrire(Abonnement $abonnement): Response
    {
        // Check if user is logged in (Sportif role assumed)
        $this->denyAccessUnlessGranted('ROLE_SPORTIF', null, 'Vous devez être connecté en tant que sportif pour vous abonner.');

        // Placeholder logic: render a subscription confirmation page
        return $this->render('sportif/souscrire.html.twig', [
            'abonnement' => $abonnement,
        ]);
    }
}