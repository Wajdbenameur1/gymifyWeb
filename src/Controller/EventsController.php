<?php
// src/Controller/EventsController.php
namespace App\Controller;

use App\Entity\Events;
use App\Entity\Salle;
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
               ->orWhere('LOWER(e.type) LIKE LOWER(:search)')
               ->orWhere('LOWER(e.reward) LIKE LOWER(:search)')
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
                    $this->addFlash('error', 'Failed to upload image.');
                    return $this->render('events/new.html.twig', [
                        'form' => $form->createView(),
                        'page_title' => 'Create New Event',
                    ]);
                }
            }

            $latitude = $event->getLatitude();
            $longitude = $event->getLongitude();
            if ($latitude === null || $longitude === null) {
                $this->addFlash('error', 'Please select a location.');
                return $this->render('events/new.html.twig', [
                    'form' => $form->createView(),
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

            try {
                $entityManager->persist($event);
                $entityManager->flush();
                $this->addFlash('success', 'Event created successfully!');
                return $this->redirectToRoute('app_events_index');
            } catch (\Exception $e) {
                $this->logger->error('Failed to create event: '.$e->getMessage());
                $this->addFlash('error', 'Error creating event.');
            }
        }

        return $this->render('events/new.html.twig', [
            'form' => $form->createView(),
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
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            return new JsonResponse([
                'id' => $event->getId(),
                'nom' => $event->getNom(),
                'date' => $event->getDate()->format('Y-m-d'),
                'heure_debut' => $event->getHeureDebut()->format('H:i'),
                'heure_fin' => $event->getHeureFin()->format('H:i'),
                'type' => $event->getType(),
                'reward' => $event->getReward(),
                'description' => $event->getDescription(),
                'lieu' => $event->getLieu(),
                'latitude' => $event->getLatitude(),
                'longitude' => $event->getLongitude(),
                'imageFile' => $event->getImageUrl()
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
                    'page_title' => 'Edit Event',
                ]);
            }

            try {
                $entityManager->flush();
                $this->addFlash('success', 'Event updated successfully!');
                return $this->redirectToRoute('app_events_index');
            } catch (\Exception $e) {
                $this->logger->error('Failed to update event: '.$e->getMessage());
                $this->addFlash('error', 'Error updating event.');
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
        return $this->render('events/show.html.twig', [
            'event' => $event,
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

            try {
                $entityManager->remove($event);
                $entityManager->flush();
                $this->addFlash('success', 'Event deleted successfully!');
            } catch (\Exception $e) {
                $this->logger->error('Failed to delete event: '.$e->getMessage());
                $this->addFlash('error', 'Error deleting event.');
            }
        }

        return $this->redirectToRoute('app_events_index');
    }
}