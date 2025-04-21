<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Enum\ActivityType;
use App\Entity\Abonnement;
use App\Entity\EquipeEvent;
use App\Entity\User;
use App\Entity\Cours;
use App\Enum\ObjectifCours;
use App\Form\SportifParticipationType;
use App\Repository\AbonnementRepository;
use App\Repository\EquipeEventRepository;
use App\Repository\SalleRepository;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

final class SportifController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

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
                    'activite' => $cour->getActivité()?->getNom(),
                    'salle' => $cour->getSalle()?->getNom(),
                ],
            ];
        }

        return $this->render('sportif/planning.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/sportif/cours', name: 'cours_sportif')]
    public function coursSportif(CoursRepository $coursRepository): Response
    {
        $cours = $coursRepository->findAll();

        return $this->render('sportif/cours.html.twig', [
            'cours' => $cours,
        ]);
    }

    #[Route('/sportif/salle/{id}', name: 'sportif_salle_details')]
    public function salleDetails(
        Salle $salle,
        AbonnementRepository $abonnementRepository,
        EquipeEventRepository $equipeEventRepository
    ): Response {
        $equipeEvents = $equipeEventRepository->findBySalle($salle);

        return $this->render('sportif/salle_details.html.twig', [
            'salle' => $salle,
            'abonnements' => $abonnementRepository->findBy(['salle' => $salle]),
            'equipe_events' => $equipeEvents,
        ]);
    }

    #[Route('/sportif/salle/{id}/events', name: 'sportif_salle_events', methods: ['GET'])]
    public function salleEvents(Salle $salle, EquipeEventRepository $equipeEventRepository): JsonResponse
    {
        $equipeEvents = $equipeEventRepository->findBySalle($salle);
        $eventsData = [];

        foreach ($equipeEvents as $equipeEvent) {
            $event = $equipeEvent->getEvent();
            $equipe = $equipeEvent->getEquipe();
            $eventsData[] = [
                'id' => $equipeEvent->getId(),
                'nom' => $event->getNom(),
                'imageUrl' => $event->getImageUrl(),
                'type' => $event->getType()?->value ?? 'N/A',
                'dateDay' => $event->getDate()->format('d'),
                'dateMonth' => $event->getDate()->format('M'),
                'heureDebut' => $event->getHeureDebut()->format('H:i'),
                'heureFin' => $event->getHeureFin()->format('H:i'),
                'reward' => $event->getReward()?->value ?? 'N/A',
                'description' => $event->getDescription(),
                'lieu' => $event->getLieu(),
                'equipeNom' => $equipe->getNom(),
                'detailsUrl' => $this->generateUrl('app_equipe_event_show', ['id' => $equipeEvent->getId()]),
                'joinUrl' => $this->generateUrl('app_equipe_event_join', ['id' => $equipeEvent->getId()]),
            ];
        }

        return new JsonResponse(['equipe_events' => $eventsData]);
    }

    #[Route('/sportif/equipe-event/join/{id}', name: 'app_equipe_event_join', methods: ['GET', 'POST'])]
    public function joinEquipeEvent(
        Request $request,
        EquipeEvent $equipeEvent,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $sportif */
        $sportif = $this->getUser();

        if (!$sportif || !in_array('ROLE_SPORTIF', $sportif->getRoles(), true)) {
            $this->addFlash('error', 'Vous devez être connecté en tant que sportif pour participer.');
            return $this->redirectToRoute('sportif_salle_details', [
                'id' => $equipeEvent->getEvent()->getSalle()->getId()
            ]);
        }

        $existingParticipation = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.id = :sportifId')
            ->andWhere('u.equipe = :equipeId')
            ->setParameter('sportifId', $sportif->getId())
            ->setParameter('equipeId', $equipeEvent->getEquipe()->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingParticipation) {
            $this->addFlash('error', 'Vous participez déjà à cette équipe pour cet événement.');
            return $this->redirectToRoute('sportif_salle_details', [
                'id' => $equipeEvent->getEvent()->getSalle()->getId()
            ]);
        }

        $equipe = $equipeEvent->getEquipe();
        if ($equipe->getNombreMembres() >= 8) {
            $this->addFlash('error', 'Cette équipe est complète (8/8).');
            return $this->redirectToRoute('sportif_salle_details', [
                'id' => $equipeEvent->getEvent()->getSalle()->getId()
            ]);
        }

        $form = $this->createForm(SportifParticipationType::class, $sportif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sportif->setEquipe($equipe);
            $equipe->setNombreMembres($equipe->getNombreMembres() + 1);

            $entityManager->persist($sportif);
            $entityManager->persist($equipe);
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'Le sportif %s %s a été ajouté à l\'équipe %s pour cet événement !',
                $sportif->getPrenom(),
                $sportif->getNom(),
                $equipe->getNom()
            ));

            return $this->redirectToRoute('sportif_salle_details', [
                'id' => $equipeEvent->getEvent()->getSalle()->getId()
            ]);
        }

        return $this->render('sportif/join_equipe_event.html.twig', [
            'form' => $form->createView(),
            'equipe_event' => $equipeEvent,
        ]);
    }

    #[Route('/sportif/equipe-event/show/{id}', name: 'app_equipe_event_show', methods: ['GET'])]
    public function showEquipeEvent(EquipeEvent $equipeEvent): Response
    {
        return $this->render('sportif/event_details.html.twig', [
            'equipe_event' => $equipeEvent,
        ]);
    }

    private function mergeDateTime(\DateTime $date, \DateTime $time): string
    {
        return $date->format('Y-m-d') . 'T' . $time->format('H:i:s');
    }

    private function getColorForObjectif(?ObjectifCours $objectif): string
    {
        return match ($objectif) {
          ObjectifCours::ENDURANCE => '#1890ff',
            ObjectifCours::PERTE_POIDS => '#52c41a',
            ObjectifCours::PRISE_DE_MASSE => '#33FF57',
            ObjectifCours::RELAXATION => '#F033FF',
            default => '#CCCCCC',
        };
    }
}
