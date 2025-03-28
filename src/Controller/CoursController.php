<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CoursController extends AbstractController
{
    #[Route('/cours', name: 'app_cours')]
    public function index(): Response
    {
        return $this->render('cours/index.html.twig', [
        'page_title' => 'Liste des cours'
            
        ]);
    }
    #[Route('/cours/new', name: 'app_cours_new')]
    public function new(Request $request): Response
{
    
    
    return $this->render('cours/new.html.twig', [
        'page_title' => 'Add New Courses',
    ]);
}
}
