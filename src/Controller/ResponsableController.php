<?php

namespace App\Controller;

use App\Repository\SalleRepository;
use App\Enum\Role;
use App\Entity\ResponsableSalle;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
final class ResponsableController extends AbstractController
{
    #[Route('/responsable', name: 'dashboard_responsable_salle')]
    public function index(): Response
    {
        $stats = [
            'events' => 150,
            'equipes' => 25,
            'salle_nom' => $this->getUser()?->getSalle()?->getNom() ?? 'Ma Salle' // Ajoutez cette ligne
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
            'equipes' => 25,
            'salle_nom' => $this->getUser()?->getSalle()?->getNom() ?? 'Ma Salle' // Ajoutez cette ligne
        ];

        return $this->render('responsable/index.html.twig', [
            'stats' => $stats,
            'page_title' => 'Tableau de bord Responsable'
        ]);
    }

    #[Route('/responsable/profile', name: 'app_responsable_profile')]
    public function profile(): Response
  
       {
           $user = $this->getUser();
           if (!$user) {
               throw $this->createAccessDeniedException('Vous devez être connecté pour voir votre profil.');
           }
   
           return $this->render('/responsable/user/show.html.twig', [
               'user' => $user,
               'page_title' => 'Mon Profil'
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
    #[Route('/responsable/mygym', name: 'app_responsable_mygym')]
    public function myGym(SalleRepository $salleRepo): Response
    {
        // 1. Vérification du rôle
        $this->denyAccessUnlessGranted('ROLE_' . strtoupper(Role::RESPONSABLE_SALLE->value));
    
        // 2. Vérification de l'utilisateur
        $user = $this->getUser();
        if (!$user instanceof ResponsableSalle) {
            throw $this->createAccessDeniedException();
        }
    
        // 3. Récupération de la salle
        $salle = $user->getSalle();
        if (!$salle) {
            $this->addFlash('warning', 'Aucune salle associée à votre compte');
            return $this->redirectToRoute('app_salle_create');
        }
    
        // 4. Chargement des relations avec une méthode existante du repository
        $freshSalle = $salleRepo->find($salle->getId()); // Utilisez find() de base
    
        // 5. Calcul des statistiques (version sécurisée)
        $stats = [
            'events' => $freshSalle->getEvents() instanceof \Countable 
                ? $freshSalle->getEvents()->count() 
                : 0,
            'equipes' => method_exists($freshSalle, 'getEquipes') 
                ? $freshSalle->getEquipes()->count() 
                : 0
        ];
    
        // 6. Rendu du template
        return $this->render('responsable/mygym.html.twig', [
            'salle' => $freshSalle,
            'page_title' => 'Ma Salle - ' . $freshSalle->getNom(),
            'stats' => $stats
        ]);
    }
   
}