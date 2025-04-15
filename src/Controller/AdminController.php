<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ActivityRepository;
use App\Repository\ReclamationRepository;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $stats = [
            'visitors' => 1294,
            'subscribers' => 1303,
            'sales' => 1345,
            'orders' => 576,
            'reclamations_total' => $reclamationRepository->count([]),
            'reclamations_pending' => $reclamationRepository->count(['statut' => 'En attente']),
            'reclamations_treated' => $reclamationRepository->count(['statut' => 'Traitée']),
        ];

        return $this->render('admin/index.html.twig', [
            'stats' => $stats,
            'page_title' => 'Tableau de bord',
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(ReclamationRepository $reclamationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $stats = [
            'visitors' => 1294,
            'subscribers' => 1303,
            'sales' => 1345,
            'orders' => 576,
            'reclamations_total' => $reclamationRepository->count([]),
            'reclamations_pending' => $reclamationRepository->count(['statut' => 'En attente']),
            'reclamations_treated' => $reclamationRepository->count(['statut' => 'Traitée']),
        ];

        return $this->render('admin/index.html.twig', [
            'stats' => $stats,
            'page_title' => 'Tableau de bord',
        ]);
    }

    #[Route('/home', name: 'app_home')]
    public function home(): Response
    {
        return new Response('Welcome to the home page');
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir votre profil.');
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'page_title' => 'Mon Profil',
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->render('security/login.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig');
    }

    #[Route('/help', name: 'app_help')]
    public function help(): Response
    {
        return $this->render('pages/help.html.twig');
    }

    #[Route('/term', name: 'app_terms')]
    public function term(): Response
    {
        return $this->render('pages/terms.html.twig');
    }

    #[Route('/admin/user', name: 'user_index')]
    public function user(): Response
    {
        return $this->render('user/index.html.twig');
    }

    #[Route('/admin/activity', name: 'app_activity')]
    public function activity(ActivityRepository $activityRepository): Response
    {
        return $this->render('activity/index.html.twig', [
            'page_title' => 'Activity Dashboard',
            'activities' => $activityRepository->findAll(),
            'stats' => [
                'visitors' => 1294,
                'subscribers' => 1303,
                'sales' => 1345,
                'orders' => 576,
            ],
        ]);
    }
}