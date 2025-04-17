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
    
        $imageFile = $form->get('imageFile')->getData();
        $webImage  = $form->get('webImage')->getData();
    
        if ($imageFile && $webImage) {
            $this->addFlash('error', 'Veuillez choisir soit une image locale, soit une URL, pas les deux.');
            return $this->render('post/new.html.twig', [
                'form' => $form->createView(),
            ]);
        }
    
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser() ?: $entityManager->getRepository(User::class)->find(1);
            $post->setUser($user)->setCreatedAt(new \DateTime());
    
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-z0-9_]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $originalFilename)));
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
    
                try {
                    // Déplacement du fichier vers le dossier absolute défini
                $targetDir    = $this->getParameter('uploads_directory');
                $imageFile->move($targetDir, $newFilename);

                // Construction du chemin absolu à stocker
                $absolutePath = $targetDir . DIRECTORY_SEPARATOR . $newFilename;
                $post->setImageUrl($absolutePath);  // Chemin web
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload');
                    return $this->render('post/new.html.twig', ['form' => $form]);
                }
            } elseif ($webImage) {
                $post->setImageUrl($webImage);
            }
    
            $entityManager->persist($post);
            $entityManager->flush();
    
            $this->addFlash('success', 'Post créé !');
            return $this->redirectToRoute('app_post_index');
        }
    
        return $this->render('post/new.html.twig', ['form' => $form]);
    }

    









    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }
































  #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET','POST'])]
public function edit(Request $request, Post $post, EntityManagerInterface $em): Response
{
    $form = $this->createForm(PostType::class, $post, [
        'attr' => ['data-parsley-validate'=>'', 'enctype'=>'multipart/form-data']
    ]);

    // Pré-remplir le champ URL si l’ancienne image est une URL
    if (!$request->isMethod('POST') && filter_var($post->getImageUrl(), FILTER_VALIDATE_URL)) {
        $form->get('webImage')->setData($post->getImageUrl());
    }

    $form->handleRequest($request);

    // Récupération des fichiers/url
    $imageFile = $form->get('imageFile')->getData();
    $webImage   = $form->get('webImage')->getData();

    // Validation mutuelle
    if ($form->isSubmitted()) {
        if ($imageFile && $webImage) {
            $this->addFlash('error', 'Veuillez choisir soit une image locale, soit une URL, pas les deux.');
        } elseif ($form->isValid()) {
            // Si nouvel upload de fichier
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-z0-9_]+/','-', strtolower(iconv('UTF-8','ASCII//TRANSLIT',$originalFilename)));
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $targetDir = $this->getParameter('uploads_directory');
                    $imageFile->move($targetDir, $newFilename);
                    $post->setImageUrl($targetDir . DIRECTORY_SEPARATOR . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l’upload de l’image.');
                    return $this->render('post/edit.html.twig', [
                        'post' => $post,
                        'form' => $form->createView(),
                    ]);
                }
            } elseif ($webImage) {
                $post->setImageUrl($webImage);
            }
            $em->flush();
            $this->addFlash('success', 'Post mis à jour !');
            return $this->redirectToRoute('app_post_index');
        }
    }

    return $this->render('post/edit.html.twig', [
        'post' => $post,
        'form' => $form->createView(),
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