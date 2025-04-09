<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ResponsableController extends AbstractController
{
    #[Route('/responsable', name: 'app_responsable')]
    public function index(): Response
    {
        $stats = [
            'events' => 150,
            'equipes' => 25
        ];

        return $this->render('responsable/index.html.twig', [
            'stats' => $stats,
            'page_title' => 'Tableau de bord Responsable'
        ]);
    }

    #[Route('/responsable/home', name: 'app_responsable_home')]
    public function home(): Response
    {
        return $this->render('responsable/home.html.twig', [
            'page_title' => 'Accueil Responsable'
        ]);
    }

    #[Route('/responsable/dashboard', name: 'app_responsable_dashboard')]
    public function dashboard(): Response
    {
        $stats = [
            'events' => 150,
            'equipes' => 25
        ];

        return $this->render('responsable/index.html.twig', [
            'stats' => $stats,
            'page_title' => 'Tableau de bord Responsable'
        ]);
    }

    #[Route('/responsable/profile', name: 'app_responsable_profile')]
    public function profile(): Response
    {
        return $this->render('responsable/profile.html.twig', [
            'page_title' => 'Profil Responsable'
        ]);
    }

    #[Route('/responsable/login', name: 'app_responsable_login')]
    public function login(): Response
    {
        return $this->render('responsable/login.html.twig', [
            'page_title' => 'Connexion Responsable'
        ]);
    }

    #[Route('/responsable/about', name: 'app_responsable_about')]
    public function about(): Response
    {
        return $this->render('responsable/about.html.twig', [
            'page_title' => 'À propos Responsable'
        ]);
    }

    #[Route('/responsable/help', name: 'app_responsable_help')]
    public function help(): Response
    {
        return $this->render('responsable/help.html.twig', [
            'page_title' => 'Aide Responsable'
        ]);
    }

    #[Route('/responsable/terms', name: 'app_responsable_terms')]
    public function terms(): Response
    {
        return $this->render('responsable/terms.html.twig', [
            'page_title' => 'Conditions d\'utilisation Responsable'
        ]);
    }
}