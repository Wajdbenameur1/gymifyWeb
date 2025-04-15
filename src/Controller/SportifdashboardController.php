<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;

final class SportifdashboardController extends AbstractController
{
    #[Route('/sportifdashboard', name: 'app_sportifdashboard')]
    public function index(): Response
    {
        return $this->render('sportifdashboard/index.html.twig', [
            'controller_name' => 'SportifdashboardController',
        ]);
    }

    #[Route('/sportifdashboard/blogs', name: 'app_sportif_blogs')]
    public function blogs(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/sportifdashboard/create-post', name: 'app_sportif_create_post')]
    public function createPost(): Response
    {
        // Redirection vers la route de création de post
        return $this->redirectToRoute('app_post_new');
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        return $this->render('sportifdashboard/profile.html.twig');
        // ⚠️ Assure-toi que le fichier "profile.html.twig" existe bien dans templates/sportifdashboard/
    }

    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->render('security/login.html.twig');
    }
}
