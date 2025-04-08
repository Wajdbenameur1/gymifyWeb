<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SportifdashboardController extends AbstractController{
    #[Route('/sportifdashboard', name: 'app_sportifdashboard')]
    public function index(): Response
    {
        return $this->render('sportifdashboard/index.html.twig', [
            'controller_name' => 'SportifdashboardController',
        ]);
    }
}
