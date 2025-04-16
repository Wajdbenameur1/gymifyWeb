<?php
namespace App\Controller;

use App\Entity\Events;
use App\Entity\Salle;
use App\Entity\Equipe;
use App\Entity\EquipeEvent;
use App\Form\EventsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class EventsController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/events', name: 'app_events_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $searchTerm = $request->query->get('search', '');

        $qb = $entityManager->getRepository(Events::class)->createQueryBuilder('e');

        if ($searchTerm) {
            $qb->where('LOWER(e.nom) LIKE LOWER(:search)')
               ->orWhere('LOWER(e.lieu) LIKE LOWER(:search)')
               ->orWhere('LOWER(e.description) LIKE LOWER(:search)')
               ->setParameter('search', '%'.$searchTerm.'%');
            $events = $qb->getQuery()->getResult();
        } else {
            $events = $entityManager->getRepository(Events::class)->findAll();
        }

        return $this->render('events/index.html.twig', [
            'events' => $events,
            'page_title' => 'List of Events',
        ]);
    }

    #[Route('/events/new', name: 'app_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Events();
        $form = $this->createForm(EventsType::class, $event);
        $teamForm = $this->createForm(\App\Form\EquipeType::class, new Equipe());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('events_images_directory'),
                        $newFilename
                    );
                    $event->setImageUrl('/uploads/events/'.$newFilename);
                } catch (\Exception $e) {
                    $this->logger->error('Failed to upload image: '.$e->getMessage());
                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Failed to upload image.',
                        ], Response::HTTP_BAD_REQUEST);
                    }
                    $this->addFlash('error', 'Failed to upload image.');
                    return $this->render('events/new.html.twig', [
                        'form' => $form->createView(),
                        'team_form' => $teamForm->createView(),
                        'page_title' => 'Create New Event',
                    ]);
                }
            }

            $latitude = $event->getLatitude();
            $longitude = $event->getLongitude();
            if ($latitude === null || $longitude === null) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Please select a location.',
                    ], Response::HTTP_BAD_REQUEST);
                }
                $this->addFlash('error', 'Please select a location.');
                return $this->render('events/new.html.twig', [
                    'form' => $form->createView(),
                    'team_form' => $teamForm->createView(),
                    'page_title' => 'Create New Event',
                ]);
            }

            $user = $this->getUser();
            if ($user) {
                $salle = $entityManager->getRepository(Salle::class)->findOneBy(['responsable' => $user]);
                if ($salle) {
                    $event->setSalle($salle);
                }
            }

            $entityManager->persist($event);
            $entityManager->flush();

            // Associate teams with the event
            $teamsData = $request->request->get('teams');
            if ($teamsData) {
                $teamIds = json_decode($teamsData, true);
                if (is_array($teamIds) && !empty($teamIds)) {
                    foreach ($teamIds as $teamId) {
                        $team = $entityManager->getRepository(Equipe::class)->find($teamId);
                        if ($team) {
                            $equipeEvent = new EquipeEvent();
                            $equipeEvent->setEvent($event);
                            $equipeEvent->setEquipe($team);
                            $entityManager->persist($equipeEvent);
                        }
                    }
                    $entityManager->flush();
                }
            }

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Event and team associations created successfully!',
                ]);
            }

            $this->addFlash('success', 'Event and team associations created successfully!');
            return $this->redirectToRoute('app_events_index');
        }

        return $this->render('events/new.html.twig', [
            'form' => $form->createView(),
            'team_form' => $teamForm->createView(),
            'page_title' => 'Create New Event',
        ]);
    }

    #[Route('/events/{id}/edit', name: 'app_events_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Events $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventsType::class, $event, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_events_edit', ['id' => $event->getId()])
        ]);
        $teamForm = $this->createForm(\App\Form\EquipeType::class, new Equipe());

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            $equipeEvents = $entityManager->getRepository(EquipeEvent::class)->findBy(['event' => $event]);
            $teams = array_map(function($equipeEvent) {
                return [
                    'id' => $equipeEvent->getEquipe()->getId(),
                    'nom' => $equipeEvent->getEquipe()->getNom(),
                ];
            }, $equipeEvents);

            return new JsonResponse([
                'id' => $event->getId(),
                'nom' => $event->getNom(),
                'date' => $event->getDate()->format('Y-m-d'),
                'heure_debut' => $event->getHeureDebut()->format('H:i'),
                'heure_fin' => $event->getHeureFin()->format('H:i'),
                'type' => $event->getType() ? $event->getType()->value : null,
                'reward' => $event->getReward() ? $event->getReward()->value : null,
                'description' => $event->getDescription(),
                'lieu' => $event->getLieu(),
                'latitude' => $event->getLatitude(),
                'longitude' => $event->getLongitude(),
                'imageFile' => $event->getImageUrl(),
                'teams' => $teams
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            $removeImage = $request->request->get('remove_image');

            if ($removeImage && $event->getImageUrl()) {
                $oldImagePath = $this->getParameter('events_images_directory').'/'.basename($event->getImageUrl());
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                $event->setImageUrl(null);
            }

            if ($imageFile) {
                if ($event->getImageUrl()) {
                    $oldImagePath = $this->getParameter('events_images_directory').'/'.basename($event->getImageUrl());
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('events_images_directory'),
                        $newFilename
                    );
                    $event->setImageUrl('/uploads/events/'.$newFilename);
                } catch (\Exception $e) {
                    $this->logger->error('Failed to upload image: '.$e->getMessage());
                    $this->addFlash('error', 'Failed to upload image.');
                    return $this->render('events/edit.html.twig', [
                        'event' => $event,
                        'form' => $form->createView(),
                        'team_form' => $teamForm->createView(),
                        'page_title' => 'Edit Event',
                    ]);
                }
            }

            $latitude = $event->getLatitude();
            $longitude = $event->getLongitude();
            if ($latitude === null || $longitude === null) {
                $this->addFlash('error', 'Please select a location.');
                return $this->render('events/edit.html.twig', [
                    'event' => $event,
                    'form' => $form->createView(),
                    'team_form' => $teamForm->createView(),
                    'page_title' => 'Edit Event',
                ]);
            }

            // Update team associations
            $teamsData = $request->request->get('teams');
            if ($teamsData) {
                $teamIds = json_decode($teamsData, true);
                // Remove existing associations
                $existingEquipeEvents = $entityManager->getRepository(EquipeEvent::class)->findBy(['event' => $event]);
                foreach ($existingEquipeEvents as $equipeEvent) {
                    $entityManager->remove($equipeEvent);
                }
                $entityManager->flush();

                // Add new associations
                if (is_array($teamIds) && !empty($teamIds)) {
                    foreach ($teamIds as $teamId) {
                        $team = $entityManager->getRepository(Equipe::class)->find($teamId);
                        if ($team) {
                            $equipeEvent = new EquipeEvent();
                            $equipeEvent->setEvent($event);
                            $equipeEvent->setEquipe($team);
                            $entityManager->persist($equipeEvent);
                        }
                    }
                }
            }

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Event and team associations updated successfully!');

            // Determine redirect based on referer
            $referer = $request->headers->get('referer', '');
            if (strpos($referer, $this->generateUrl('app_events_index')) !== false) {
                return $this->redirectToRoute('app_events_index');
            }
            return $this->redirectToRoute('app_equipe_event');
        }

        return $this->render('events/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'team_form' => $teamForm->createView(),
            'page_title' => 'Edit Event',
        ]);
    }

    #[Route('/events/{id}', name: 'app_events_show', methods: ['GET'])]
    public function show(Events $event, EntityManagerInterface $entityManager): Response
    {
        $equipeEvents = $entityManager->getRepository(EquipeEvent::class)->findBy(['event' => $event]);
        $teams = array_map(fn($equipeEvent) => $equipeEvent->getEquipe(), $equipeEvents);

        return $this->render('events/show.html.twig', [
            'event' => $event,
            'teams' => $teams,
            'page_title' => 'Event Details - ' . $event->getNom(),
        ]);
    }

    #[Route('/events/{id}/delete', name: 'app_events_delete', methods: ['POST'])]
    public function delete(Request $request, Events $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            if ($event->getImageUrl()) {
                $imagePath = $this->getParameter('events_images_directory').'/'.basename($event->getImageUrl());
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Remove associated EquipeEvent entries
            $equipeEvents = $entityManager->getRepository(EquipeEvent::class)->findBy(['event' => $event]);
            foreach ($equipeEvents as $equipeEvent) {
                $entityManager->remove($equipeEvent);
            }

            $entityManager->remove($event);
            $entityManager->flush();

            $this->addFlash('success', 'Event deleted successfully!');
        }

        return $this->redirectToRoute('app_events_index');
    }
}