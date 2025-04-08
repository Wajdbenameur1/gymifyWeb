<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;






final class DashSportifController extends AbstractController
{
    #[Route('/dashsportif', name: 'app_dashsportif')]
    public function index(): Response
    {
      $stats = [
        'visitors' => 1294,
        'subscribers' => 1303,
        'sales' => 1345,
        'orders' => 576
    ];

    return $this->render('dashsportif/index.html.twig', [
        'stats' => $stats,
        'page_title' => 'Tableau de bord'
    ]);
    }
   
       
       
      
        
  }


       

    
    

