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
use Symfony\Component\HttpFoundation\JsonResponse;



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
















    
    
    #[Route('/{postId}', name: 'app_comment_new', methods: ['POST'])]
    public function new(Request $request, PostRepository $postRepo, EntityManagerInterface $em, int $postId): JsonResponse|RedirectResponse
    {
        $post = $postRepo->find($postId);
        if (!$post) {
            return new JsonResponse(['errors' => ['Post introuvable']], 404);
        }
    
        $content = $request->request->get('content');
    
        if (!$content || strlen(trim($content)) < 2) {
            return new JsonResponse(['errors' => ['Le commentaire est trop court']], 400);
        }
    
        $comment = new Comment();
        $comment->setContent($content);
        $comment->setPost($post);
        $comment->setUser($this->getUser());
        $comment->setCreatedAt(new \DateTime());
    
        $em->persist($comment);
        $em->flush();
    
        // Réponse AJAX
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('d M Y à H:i'),
                'user' => [
                    'nom' => $comment->getUser()->getNom(),
                    'avatar' => $comment->getUser()->getImageUrl()
                        ? $this->get('assets.packages')->getUrl($comment->getUser()->getImageUrl())
                        : $this->get('assets.packages')->getUrl('img/screen/user.png')
                ]
            ]);
        }
    
        // Si jamais appel non AJAX, fallback (optionnel)
        return $this->redirectToRoute('app_post_show', ['id' => $postId]);
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





















    #[Route('/delete/{id}', name: 'app_comment_delete', methods: ['POST'])]
public function delete(
    Request $request,
    CommentRepository $commentRepo,
    int $id,
    EntityManagerInterface $entityManager
): Response {
    $comment = $commentRepo->find($id);

    if (!$comment) {
        throw $this->createNotFoundException('Commentaire introuvable');
    }

    // Vérifie que l'utilisateur connecté est bien le propriétaire du commentaire
    if ($comment->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce commentaire.');
    }

    // Vérifie le token CSRF
    if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
        $entityManager->remove($comment);
        $entityManager->flush();

        // Si la requête est AJAX, on renvoie une réponse vide (204 No Content)
        if ($request->isXmlHttpRequest()) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }
    }

    // Sinon, redirection classique (fallback, au cas où)
    return $this->redirectToRoute('app_post_show', [
        'id' => $comment->getPost()->getId()
    ], Response::HTTP_SEE_OTHER);
}
























}