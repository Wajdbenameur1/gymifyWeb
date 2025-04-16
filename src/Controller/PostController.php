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
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si un utilisateur est connecté
            $user = $this->getUser();
    
            // Si l'utilisateur n'est pas connecté, utilise l'utilisateur avec l'ID 1
            if (!$user) {
                $user = $entityManager->getRepository(User::class)->find(1);
                if (!$user) {
                    throw new \Exception("L'utilisateur par défaut avec l'ID 1 n'existe pas.");
                }
            }
    
            // Traitement de l'image (si nécessaire)
// Traitement de l'image (si nécessaire)
$imageFile = $form->get('imageFile')->getData();
if ($imageFile) {
    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = $originalFilename; // Tu peux ajouter un slugger si nécessaire
    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

    try {
        // Move the uploaded file to the desired directory and get the absolute path
        $imageFile->move(
            $this->getParameter('uploads_directory'),
            $newFilename
        );
        
        // Save the absolute path in the database (update the image URL)
        $imageUrl = $this->getParameter('uploads_directory') . '\\' . $newFilename;
        $post->setImageUrl($imageUrl);
    } catch (FileException $e) {
        // Handle the error if necessary
        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
    }
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