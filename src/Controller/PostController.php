<?php

namespace App\Controller;
use App\Entity\User;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/post')]
final class PostController extends AbstractController
{
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $post->setTitle('Test');
        $post->setContent('Contenu test');
        $post->setCreatedAt(new \DateTime());

        // Vérifiez si un utilisateur est connecté
        $user = $this->getUser();

        // Si l'utilisateur n'est pas connecté, affectez un utilisateur par défaut (id = 1)
        if (!$user) {
            dump($entityManager->getRepository(User::class)); die;
            throw new \Exception("L'utilisateur par défaut avec l'ID 1 n'existe pas.");
            // Vérifiez si l'utilisateur avec l'ID 1 existe
            if (!$user) {
                // Si l'utilisateur par défaut n'existe pas, gérez l'exception
                throw new \Exception("L'utilisateur par défaut avec l'ID 1 n'existe pas.");
            }
        }

        // Affecte l'id_User au post
        $post->setUser($user);

        // Persiste l'entité post dans la base de données
        $entityManager->persist($post);
        $entityManager->flush();

        // Ajouter un message flash pour confirmer la création du post
        $this->addFlash('success', 'Le post a été créé avec succès.');

        return $this->redirectToRoute('app_post_index');
    }













    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }













    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}  