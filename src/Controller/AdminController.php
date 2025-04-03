<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ActivityRepository; 
use App\Entity\Activité; 





final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
      $stats = [
        'visitors' => 1294,
        'subscribers' => 1303,
        'sales' => 1345,
        'orders' => 576
    ];

    return $this->render('admin/index.html.twig', [
        'stats' => $stats,
        'page_title' => 'Tableau de bord'
    ]);
    }
    #[Route('/home', name: 'app_home')]
      public function home()
      {
    // Your controller code
       }
       #[Route('/dashboard', name:'app_dashboard')]
      public function dashboard()
      {
        $stats = [
          'visitors' => 1294,
          'subscribers' => 1303,
          'sales' => 1345,
          'orders' => 576
      ];
  
      return $this->render('admin/index.html.twig', [
          'stats' => $stats,
          'page_title' => 'Tableau de bord'
      ]);
        
    
       }


       #[Route('/profile', name:'app_profile')]
      public function profile()
      {
    
       }
       #[Route('/login', name:'app_login')]
      public function login()
      {
    
       }
       #[Route('/about', name:'app_about')]
      public function about()
      {
    
       }
       #[Route('/help', name:'app_help')]
      public function help()
      {
    
       }
       #[Route('/term', name:'app_terms')]
       public function term()
       {
     
        }
        #[Route('/admin/users', name:'app_users')]
       public function user()
       {
        return $this->render('admin/users.html.twig', [ ]);
        }
        #[Route('/admin/activity', name:'app_activity')]
       public function activity(ActivityRepository $activityRepository): Response
       { return $this->render('activity/index.html.twig', [
          'page_title' => 'Activity Dashboard',
          'activities' => $activityRepository->findAll(),
          'stats' => [
                'visitors' => 1294,
                'subscribers' => 1303,
                'sales' => 1345,
                'orders' => 576
            ]
      ]);
       }
     
  }


       

    
    

