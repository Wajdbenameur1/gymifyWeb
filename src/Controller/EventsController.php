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
    private $entityManager;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    #[Route('/events', name: 'app_events_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchTerm = $request->query->get('search', '');

        $qb = $this->entityManager->getRepository(Events::class)->createQueryBuilder('e');

        if ($searchTerm) {
            $qb->where('LOWER(e.nom) LIKE LOWER(:search)')
               ->orWhere('LOWER(e.lieu) LIKE LOWER(:search)')
               ->orWhere('LOWER(e.description) LIKE LOWER(:search)')
               ->setParameter('search', '%' . $searchTerm . '%');
            $events = $qb->getQuery()->getResult();
        } else {
            $events = $this->entityManager->getRepository(Events::class)->findAll();
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array_map(function($event) {
                return [
                    'id' => $event->getId(),
                    'nom' => $event->getNom(),
                ];
            }, $events));
        }

        return $this->render('events/index.html.twig', [
            'events' => $events,
            'page_title' => 'List of Events',
        ]);
    }

    #[Route('/events/new', name: 'app_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User must be logged in to create an event.');

        $event = new Events();
        $form = $this->createForm(EventsType::class, $event, ['is_edit' => false]);
        $teamForm = $this->createForm(\App\Form\EquipeType::class, new Equipe());

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $teamsData = $request->request->get('teams');
            $teamIds = $teamsData ? json_decode($teamsData, true) : [];
            $errors = [];

            // Validate teams
            if (empty($teamIds)) {
                $errors['teams'] = ['At least one team is required.'];
            }

            if ($form->isValid() && empty($errors)) {
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                    try {
                        $imageFile->move(
                            $this->getParameter('events_images_directory'),
                            $newFilename
                        );
                        $event->setImageUrl('/Uploads/events/' . $newFilename);
                    } catch (\Exception $e) {
                        $this->logger->error('Failed to upload image: ' . $e->getMessage());
                        if ($request->isXmlHttpRequest()) {
                            return new JsonResponse([
                                'success' => false,
                                'message' => 'Failed to upload image.',
                                'errors' => ['imageFile' => ['Failed to upload image.']],
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

                $user = $this->getUser();
                if ($user) {
                    $salle = $this->entityManager->getRepository(Salle::class)->findOneBy(['responsable' => $user]);
                    if ($salle) {
                        $event->setSalle($salle);
                    }
                }

                try {
                    $this->entityManager->persist($event);
                    $this->entityManager->flush();

                    // Associate teams with the event
                    foreach ($teamIds as $teamId) {
                        $team = $this->entityManager->getRepository(Equipe::class)->find((int)$teamId);
                        if ($team) {
                            $equipeEvent = new EquipeEvent();
                            $equipeEvent->setEvent($event);
                            $equipeEvent->setEquipe($team);
                            $this->entityManager->persist($equipeEvent);
                        } else {
                            $this->logger->warning("Team with ID $teamId not found.");
                        }
                    }
                    $this->entityManager->flush();

                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse([
                            'success' => true,
                            'message' => 'Event created successfully!',
                            'eventId' => $event->getId(),
                        ]);
                    }

                    $this->addFlash('success', 'Event created successfully!');
                    return $this->redirectToRoute('app_events_index');
                } catch (\Exception $e) {
                    $this->logger->error('Error saving event: ' . $e->getMessage());
                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Error saving event.',
                            'errors' => ['general' => ['An error occurred while saving the event.']],
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    $this->addFlash('error', 'Error saving event.');
                    return $this->render('events/new.html.twig', [
                        'form' => $form->createView(),
                        'team_form' => $teamForm->createView(),
                        'page_title' => 'Create New Event',
                    ]);
                }
            } else {
                $formErrors = $this->getFormErrors($form);
                $allErrors = array_merge($formErrors, $errors);

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Invalid form data.',
                        'errors' => $allErrors,
                    ], Response::HTTP_BAD_REQUEST);
                }

                $this->addFlash('error', 'Failed to create the event.');
                return $this->render('events/new.html.twig', [
                    'form' => $form->createView(),
                    'team_form' => $teamForm->createView(),
                    'page_title' => 'Create New Event',
                ]);
            }
        }

        return $this->render('events/new.html.twig', [
            'form' => $form->createView(),
            'team_form' => $teamForm->createView(),
            'page_title' => 'Create New Event',
        ]);
    }

    #[Route('/events/{id}/edit', name: 'app_events_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Events $event): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User must be logged in to edit an event.');

        $form = $this->createForm(EventsType::class, $event, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_events_edit', ['id' => $event->getId()]),
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            $equipeEvents = $this->entityManager->getRepository(EquipeEvent::class)->findBy(['event' => $event]);
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
                'teams' => $teams,
            ]);
        }

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $imageFile = $form->get('imageFile')->getData();
                $removeImage = $request->request->get('remove_image');

                if ($removeImage && $event->getImageUrl()) {
                    $oldImagePath = $this->getParameter('events_images_directory') . '/' . basename($event->getImageUrl());
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                    $event->setImageUrl(null);
                }

                if ($imageFile) {
                    if ($event->getImageUrl()) {
                        $oldImagePath = $this->getParameter('events_images_directory') . '/' . basename($event->getImageUrl());
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                    try {
                        $imageFile->move(
                            $this->getParameter('events_images_directory'),
                            $newFilename
                        );
                        $event->setImageUrl('/Uploads/events/' . $newFilename);
                    } catch (\Exception $e) {
                        $this->logger->error('Failed to upload image: ' . $e->getMessage());
                        if ($request->isXmlHttpRequest()) {
                            return new JsonResponse([
                                'success' => false,
                                'message' => 'Failed to upload image.',
                                'errors' => ['imageFile' => ['Failed to upload image.']],
                            ], Response::HTTP_BAD_REQUEST);
                        }
                        $this->addFlash('error', 'Failed to upload image.');
                        return $this->render('events/edit.html.twig', [
                            'event' => $event,
                            'form' => $form->createView(),
                            'page_title' => 'Edit Event',
                        ]);
                    }
                }

                try {
                    $this->entityManager->persist($event);
                    $this->entityManager->flush();

                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse([
                            'success' => true,
                            'message' => 'Event updated successfully!',
                            'redirect' => $this->generateUrl('app_events_index'),
                        ]);
                    }

                    $this->addFlash('success', 'Event updated successfully!');
                    return $this->redirectToRoute('app_events_index');
                } catch (\Exception $e) {
                    $this->logger->error('Error updating event: ' . $e->getMessage());
                    if ($request->isXmlHttpRequest()) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Error updating event.',
                            'errors' => ['general' => ['An error occurred while updating the event.']],
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                    $this->addFlash('error', 'Error updating event.');
                    return $this->render('events/edit.html.twig', [
                        'event' => $event,
                        'form' => $form->createView(),
                        'page_title' => 'Edit Event',
                    ]);
                }
            } else {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Invalid form data.',
                        'errors' => $this->getFormErrors($form),
                    ], Response::HTTP_BAD_REQUEST);
                }

                $this->addFlash('error', 'Failed to update the event.');
                return $this->render('events/edit.html.twig', [
                    'event' => $event,
                    'form' => $form->createView(),
                    'page_title' => 'Edit Event',
                ]);
            }
        }

        return $this->render('events/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'page_title' => 'Edit Event',
        ]);
    }

    #[Route('/events/{id}', name: 'app_events_show', methods: ['GET'])]
    public function show(Events $event): Response
    {
        $equipeEvents = $this->entityManager->getRepository(EquipeEvent::class)->findBy(['event' => $event]);
        $teams = array_map(fn($equipeEvent) => $equipeEvent->getEquipe(), $equipeEvents);

        return $this->render('events/show.html.twig', [
            'event' => $event,
            'teams' => $teams,
            'page_title' => 'Event Details - ' . $event->getNom(),
        ]);
    }

    #[Route('/events/{id}/delete', name: 'app_events_delete', methods: ['POST'])]
    public function delete(Request $request, Events $event): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User must be logged in to delete an event.');

        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            try {
                if ($event->getImageUrl()) {
                    $imagePath = $this->getParameter('events_images_directory') . '/' . basename($event->getImageUrl());
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }

                $equipeEvents = $this->entityManager->getRepository(EquipeEvent::class)->findBy(['event' => $event]);
                foreach ($equipeEvents as $equipeEvent) {
                    $this->entityManager->remove($equipeEvent);
                }

                $this->entityManager->remove($event);
                $this->entityManager->flush();

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Event deleted successfully!',
                    ]);
                }

                $this->addFlash('success', 'Event deleted successfully!');
                return $this->redirectToRoute('app_events_index');
            } catch (\Exception $e) {
                $this->logger->error('Error deleting event: ' . $e->getMessage());
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Error deleting event.',
                        'errors' => ['general' => ['An error occurred while deleting the event.']],
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                $this->addFlash('error', 'Error deleting event.');
                return $this->redirectToRoute('app_events_index');
            }
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid CSRF token.',
                'errors' => ['general' => ['Invalid CSRF token.']],
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->addFlash('error', 'Invalid CSRF token.');
        return $this->redirectToRoute('app_events_index');
    }

    private function getFormErrors(\Symfony\Component\Form\FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            if (!isset($errors[$field])) {
                $errors[$field] = [];
            }
            $errors[$field][] = $error->getMessage();
        }
        return $errors;
    }
}