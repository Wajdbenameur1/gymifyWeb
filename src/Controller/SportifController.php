<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CoursRepository;
use App\Entity\Cours;
use App\Enum\ObjectifCours;



final class SportifController extends AbstractController
{
  #[Route('/sportif', name: 'home')]
  public function sportif(): Response
  {
      return $this->render('sportif/index.html.twig');
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

}