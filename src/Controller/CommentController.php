<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;                // <- Assurez‑vous d’importer LE BON namespace
use Symfony\Component\Validator\Validator\ValidatorInterface;



#[Route('/comment')]
final class CommentController extends AbstractController
{
    #[Route('/', name: 'app_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }



    

    #[Route('/new/{postId}', name: 'app_comment_new', methods: ['POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        PostRepository $postRepo,
        ValidatorInterface $validator,  // Injection du service Validator
        int $postId
    ): Response {
        // Trouver le post
        $post = $postRepo->find($postId);
        if (!$post) {
            throw $this->createNotFoundException('Le post n\'existe pas.');
        }
    
        // Récupérer le contenu du commentaire
        $content = trim($request->request->get('content', ''));
    
        // Créer l'objet Comment
        $comment = new Comment();
        $comment->setPost($post)
                ->setUser($this->getUser())
                ->setContent($content)
                ->setCreatedAt(new \DateTime());
    
        // ✅ Validation
        $errors = $validator->validate($comment);  // On valide l'entité
    
        if (count($errors) > 0) {
            // Si des erreurs existent, on les récupère
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();  // Ajout des messages d'erreur
            }
    
            // Si c'est une requête AJAX, on renvoie les erreurs en JSON
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['errors' => $errorMessages], 400);  // Code 400 pour erreur
            }
    
            // Sinon, ajout des erreurs dans les flash messages
            $this->addFlash('error', implode(' ', $errorMessages));
    
            // Redirection avec un message flash
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_post_index'));
        }
    
        // ✅ Aucun problème → on persiste
        $em->persist($comment);
        $em->flush();
    
        // Si c'est une requête AJAX, renvoie les données du commentaire créé
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'id'        => $comment->getId(),
                'content'   => $comment->getContent(),
                'user'      => [
                    'nom'    => $comment->getUser()->getNom(),
                    'avatar' => $comment->getUser()->getImageUrl()
                        ? $this->getParameter('uploads_base_url').'/'.$comment->getUser()->getImageUrl()
                        : $this->getParameter('app.base_path').'/img/screen/user.png',
                ],
                'createdAt' => $comment->getCreatedAt()->format('d M Y à H:i'),
            ]);
        }
    
        // Redirection classique après succès
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_post_index'));
    }













    #[Route('/{id}', name: 'app_comment_show', methods: ['GET'])]
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur actuel est celui qui a créé le commentaire (optionnel)
        if ($comment->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce commentaire.');
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur est bien celui qui a créé le commentaire (optionnel)
        if ($comment->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce commentaire.');
        }

        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()], Response::HTTP_SEE_OTHER);
    }
}