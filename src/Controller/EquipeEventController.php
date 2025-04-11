<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EquipeEventController extends AbstractController{
    #[Route('/equipe/event', name: 'app_equipe_event')]
    public function index(): Response
    {
        return $this->render('equipe_event/index.html.twig', [
            'controller_name' => 'EquipeEventController',
        ]);
    }
}
