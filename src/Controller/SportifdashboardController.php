<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\PostFilterType;

final class SportifdashboardController extends AbstractController{
    #[Route('/sportifdashboard', name: 'app_sportifdashboard')]
    public function index(): Response
    {
        return $this->render('sportifdashboard/index.html.twig', [
            'controller_name' => 'SportifdashaboardController',
        ]);
    }

    #[Route('/sportifdashboard/blogs', name: 'app_sportif_blogs')]
    public function blogs(Request $request, PostRepository $postRepository, PaginatorInterface $paginator): Response
    {
        // Create the filter form
        $form = $this->createForm(PostFilterType::class);
        $form->handleRequest($request);
        
        // Apply filters
        $filters = $form->isSubmitted() && $form->isValid() ? $form->getData() : [];
        
        // Get filtered query
        $query = $postRepository->findByFilters($filters);
        
        // Determine items per page
        $itemsPerPage = isset($filters['itemsPerPage']) ? (int)$filters['itemsPerPage'] : 4;
        
        // Paginate the results
        $posts = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );
        
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'reactionTypes' => \App\Entity\Reactions::TYPES,
            'filter_form' => $form->createView(),
        ]);
    }

    #[Route('/sportifdashboard/create-post', name: 'app_sportif_create_post')]
    public function createPost(): Response
    {
        // Redirection vers la route de création deee post
        return $this->redirectToRoute('app_post_new');
    }
    

       }
  


       

