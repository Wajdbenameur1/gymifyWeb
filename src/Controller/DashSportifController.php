<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashSportifController extends AbstractController{
    #[Route('/dash/sportif', name: 'app_dash_sportif')]
    public function index(): Response
    {
        return $this->render('dash_sportif/index.html.twig', [
            'controller_name' => 'DashSportifController',
        ]);
    }
}
