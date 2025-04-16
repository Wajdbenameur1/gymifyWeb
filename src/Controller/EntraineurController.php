<?php

namespace App\Controller;
use App\Entity\Cours;
use App\Service\GoogleCalendarSyncService;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use App\Service\GoogleCalendarService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Enum\ObjectifCours;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Planning;

final class EntraineurController extends AbstractController
{
  #[Route('/calendar', name: 'app_calendar')]
public function calendar(
    Request $request,
    EntityManagerInterface $entityManager,
    GoogleCalendarService $googleCalendarService,
    SessionInterface $session
): Response {
    // 1️⃣ Récupérer les cours depuis la base de données
    $coursRepository = $entityManager->getRepository(Cours::class);
    $coursList = $coursRepository->findAll();
    
    $events = [];
    
    foreach ($coursList as $cours) {
        $events[] = [
            'title' => $cours->getTitle(),
            'start' => $cours->getDateDebut()->format('Y-m-d').'T'.$cours->getHeurDebut()->format('H:i:s'),
            'end' => $cours->getDateDebut()->format('Y-m-d').'T'.$cours->getHeurFin()->format('H:i:s'),
            'description' => $cours->getDescription(),
            'backgroundColor' => $this->getColorForObjectif($cours->getObjectif()),
        ];
    }

    // 2️⃣ Récupérer les événements de Google Calendar
    $accessToken = $session->get('google_access_token');
    if ($accessToken) {
        $client = $googleCalendarService->getClient();
        $client->setAccessToken($accessToken);

        $service = new Google_Service_Calendar($client);
        $googleEventsList = $service->events->listEvents('primary')->getItems();

        foreach ($googleEventsList as $event) {
            if (!$event->getStart() || !$event->getEnd()) {
                continue;
            }

            $events[] = [
                'title' => $event->getSummary(),
                'start' => $event->getStart()->getDateTime() ?? $event->getStart()->getDate(),
                'end' => $event->getEnd()->getDateTime() ?? $event->getEnd()->getDate(),
                'description' => $event->getDescription(),
                'backgroundColor' => '#4285F4', // Bleu Google Calendar
            ];
        }
    }

    return $this->render('cours/calendar.html.twig', [
        'page_title' => 'Mon Calendrier',
        'events' => json_encode($events),
    ]);
}

  #[Route('/entraineur', name: 'app_entraineur')]
  public function index(EntityManagerInterface $entityManager): Response
{
    $plannings = $entityManager->getRepository(Planning::class)->findAll();
    
    return $this->render('planning/index.html.twig', [
        'plannings' => $plannings,
        'page_title' => 'Liste des plannings'
    ]);
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

