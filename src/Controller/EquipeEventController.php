<?php
namespace App\Controller;

use App\Entity\EquipeEvent;
use App\Entity\Events;
use App\Entity\Equipe;
use App\Form\EventsType;
use App\Form\EquipeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class EquipeEventController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/equipe/event', name: 'app_equipe_event', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $equipeEvents = $entityManager->getRepository(EquipeEvent::class)->findAll();

        return $this->render('equipe_event/index.html.twig', [
            'equipe_events' => $equipeEvents,
            'page_title' => 'List of Teams Events',
        ]);
    }

    #[Route('/equipe/event/{id}', name: 'app_equipe_event_show1', methods: ['GET'])]
    public function show(EquipeEvent $equipeEvent): Response
    {
        return $this->render('equipe_event/show.html.twig', [
            'equipe_event' => $equipeEvent,
            'page_title' => 'Team Event Details - ' . $equipeEvent->getEvent()->getNom(),
        ]);
    }

    #[Route('/equipe/event/{id}/edit', name: 'app_equipe_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EquipeEvent $equipeEvent, EntityManagerInterface $entityManager): Response
    {
        $event = $equipeEvent->getEvent();
        $team = $equipeEvent->getEquipe();

        // Create the event form
        $form = $this->createForm(EventsType::class, $event, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_equipe_event_edit', ['id' => $equipeEvent->getId()])
        ]);

        // Create the team form, pre-filled with the associated team's data
        $teamForm = $this->createForm(EquipeType::class, $team, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_equipe_edit', ['id' => $team->getId()])
        ]);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            $equipeEvents = $entityManager->getRepository(EquipeEvent::class)->findBy(['event' => $event]);
            $teams = array_map(function($eqEvent) {
                $team = $eqEvent->getEquipe();
                return [
                    'id' => $team->getId(),
                    'nom' => $team->getNom(),
                    'niveau' => $team->getNiveau() ? $team->getNiveau()->value : null,
                    'nombre_membres' => $team->getNombreMembres(),
                    'imageFile' => $team->getImageUrl(),
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
                    return $this->render('equipe_event/edit.html.twig', [
                        'equipe_event' => $equipeEvent,
                        'form' => $form->createView(),
                        'team_form' => $teamForm->createView(),
                        'page_title' => 'Edit Team Event',
                    ]);
                }
            }

            $latitude = $event->getLatitude();
            $longitude = $event->getLongitude();
            if ($latitude === null || $longitude === null) {
                $this->addFlash('error', 'Please select a location.');
                return $this->render('equipe_event/edit.html.twig', [
                    'equipe_event' => $equipeEvent,
                    'form' => $form->createView(),
                    'team_form' => $teamForm->createView(),
                    'page_title' => 'Edit Team Event',
                ]);
            }

            // Update team associations
            $teamsData = $request->request->get('teams');
            if ($teamsData) {
                $teamIds = json_decode($teamsData, true);
                // Remove existing associations for this event
                $existingEquipeEvents = $entityManager->getRepository(EquipeEvent::class)->findBy(['event' => $event]);
                foreach ($existingEquipeEvents as $eqEvent) {
                    if (!in_array($eqEvent->getEquipe()->getId(), $teamIds)) {
                        $entityManager->remove($eqEvent);
                    }
                }
                $entityManager->flush();

                // Add new associations
                if (is_array($teamIds) && !empty($teamIds)) {
                    foreach ($teamIds as $teamId) {
                        $team = $entityManager->getRepository(Equipe::class)->find($teamId);
                        if ($team && !$entityManager->getRepository(EquipeEvent::class)->findOneBy(['event' => $event, 'equipe' => $team])) {
                            $newEquipeEvent = new EquipeEvent();
                            $newEquipeEvent->setEvent($event);
                            $newEquipeEvent->setEquipe($team);
                            $entityManager->persist($newEquipeEvent);
                        }
                    }
                }
            }

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Team event associations updated successfully!');

            return $this->redirectToRoute('app_equipe_event');
        }

        return $this->render('equipe_event/edit.html.twig', [
            'equipe_event' => $equipeEvent,
            'form' => $form->createView(),
            'team_form' => $teamForm->createView(),
            'page_title' => 'Edit Team Event',
        ]);
    }

    #[Route('/equipe/event/{id}/delete', name: 'app_equipe_event_delete', methods: ['POST'])]
    public function delete(Request $request, EquipeEvent $equipeEvent, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$equipeEvent->getId(), $request->request->get('_token'))) {
            $entityManager->remove($equipeEvent);
            $entityManager->flush();
            $this->addFlash('success', 'Team event association deleted successfully!');
        }

        return $this->redirectToRoute('app_equipe_event');
    }
}