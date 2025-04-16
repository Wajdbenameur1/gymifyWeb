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
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        // Récupération des deux champs non mappés
        $imageFile = $form->get('imageFile')->getData();
        $webImage  = $form->get('webImage')->getData();

        // Vérification si les deux sources d'image sont renseignées
        if ($imageFile && $webImage) {
            $this->addFlash('error', 'Veuillez choisir soit une image locale, soit une URL, pas les deux.');
            return $this->render('post/new.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération de l'utilisateur connecté ou défaut
            $user = $this->getUser() ?: $entityManager->getRepository(User::class)->find(1);
            if (!$user) {
                throw new \Exception("L'utilisateur par défaut avec l'ID 1 n'existe pas.");
            }

            // Traitement de l'upload local
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename     = $originalFilename; // Vous pouvez utiliser un service slug pour plus de sécurité
                $newFilename      = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $post->setImageUrl($this->getParameter('uploads_directory') . DIRECTORY_SEPARATOR . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image locale.');
                    return $this->render('post/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            } elseif ($webImage) {
                // Traitement de l'image via une URL
                $post->setImageUrl($webImage);
            }

            $post->setUser($user);
            $post->setCreatedAt(new \DateTime());

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Le post a été créé avec succès.');
            return $this->redirectToRoute('app_post_index');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
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