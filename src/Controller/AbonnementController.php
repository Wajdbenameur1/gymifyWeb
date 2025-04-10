<?php

namespace App\Controller;

use App\Entity\Abonnement;
use App\Form\AbonnementType;
use App\Repository\AbonnementRepository;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/responsable/abonnement')]
#[IsGranted('ROLE_RESPONSABLE')]
class AbonnementController extends AbstractController
{
    #[Route('/', name: 'responsable_abonnement_index', methods: ['GET'])]
    public function index(AbonnementRepository $abonnementRepository): Response
    {
        $salle = $this->getUser()->getSalle();
        $abonnements = $abonnementRepository->findBySalle($salle);

        return $this->render('abonnement/index.html.twig', [
            'abonnements' => $abonnements,
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
            'activites' => $activityRepository->findAll() // Fetch all activities
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
            'activites' => $activityRepository->findAll() // Fetch all activities
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

    #[Route('/{id}', name: 'responsable_abonnement_delete', methods: ['POST'])]
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

    #[Route('/{id}', name: 'responsable_abonnement_show', methods: ['GET'])]
    public function show(Abonnement $abonnement): Response
    {
        $salle = $this->getUser()->getSalle();
        if ($abonnement->getSalle() !== $salle) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('abonnement/show.html.twig', [
            'abonnement' => $abonnement,
        ]);
    }}