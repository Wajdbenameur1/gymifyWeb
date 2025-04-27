<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Service\EmailJSService;
use App\Service\PerspectiveApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;                // <- Assurez‑vous d'importer LE BON namespace
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Psr\Log\LoggerInterface;


#[Route('/comment')]
final class CommentController extends AbstractController
{
    public function __construct(
        private readonly PerspectiveApiService $perspectiveApiService,
        private readonly EmailJSService $emailJSService,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route('/', name: 'app_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }














    #[Route('/{postId}', name: 'app_comment_new', methods: ['POST'])]
    public function new(
        Request $request,
        PostRepository $postRepo,
        EntityManagerInterface $em,
        int $postId
    ): JsonResponse {
        // Log du token reçu
        $this->logger->info('Token reçu: ' . $request->request->get('_token'));
        $this->logger->info('Token attendu pour comment' . $postId);
        
        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('comment' . $postId, $request->request->get('_token'))) {
            $this->logger->error('Token CSRF invalide. Token reçu: ' . $request->request->get('_token'));
            return new JsonResponse(['error' => 'Token invalide'], Response::HTTP_BAD_REQUEST);
        }

        // Vérification du post
        $post = $postRepo->find($postId);
        if (!$post) {
            return new JsonResponse(['error' => 'Post introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Vérification du contenu
        $content = trim($request->request->get('content', ''));
        if (empty($content)) {
            return new JsonResponse(['error' => 'Le commentaire ne peut pas être vide'], Response::HTTP_BAD_REQUEST);
        }
        if (strlen($content) < 3) {
            return new JsonResponse(['error' => 'Le commentaire doit contenir au moins 3 caractères'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Analyse de la toxicité du commentaire
            $this->logger->info('Début de l\'analyse de toxicité pour le commentaire: ' . $content);
            $toxicityScore = $this->perspectiveApiService->analyzeCommentToxicity($content);
            $this->logger->info('Score de toxicité reçu: ' . $toxicityScore);
            
            $user = $this->getUser();
            if (!$user) {
                $this->logger->error('Aucun utilisateur connecté');
                return new JsonResponse(['error' => 'Vous devez être connecté pour poster un commentaire'], Response::HTTP_UNAUTHORIZED);
            }

            if ($toxicityScore > 0.6) {
                $this->logger->info('Commentaire trop toxique (score > 0.6), suppression');
                // Commentaire trop toxique - envoi d'email et suppression
                $this->emailJSService->sendEmail(
                    $user->getEmail(),
                    '⚠️ Commentaire supprimé pour non-respect des règles',
                    'Votre commentaire a été jugé toxique et a été supprimé.'
                );
                
                return new JsonResponse([
                    'error' => 'Commentaire supprimé pour toxicité excessive',
                    'toxicityScore' => $toxicityScore
                ], Response::HTTP_FORBIDDEN);
            }

            if ($toxicityScore > 0.4) {
                $this->logger->info('Commentaire sensible (0.4 < score <= 0.6), modification');
                // Commentaire sensible - envoi d'email et modification du contenu
                $this->emailJSService->sendEmail(
                    $user->getEmail(),
                    '⚠️ Votre commentaire a été modéré',
                    'Votre commentaire contient un contenu sensible. Veuillez le modifier si nécessaire.'
                );
                
                $content = '⚠️ Votre commentaire contient un contenu sensible. Veuillez le modifier si nécessaire.';
            } else {
                $this->logger->info('Commentaire acceptable (score <= 0.4)');
            }

            // Création du commentaire
            $this->logger->info('Création du commentaire pour le post ' . $postId);
            $comment = new Comment();
            $comment->setContent($content)
                   ->setPost($post)
                   ->setUser($user)
                   ->setCreatedAt(new \DateTime());

            $em->persist($comment);
            $em->flush();
            $this->logger->info('Commentaire créé avec succès, ID: ' . $comment->getId());

            // Préparation de la réponse
            $response = [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('d M Y à H:i'),
                'user' => [
                    'nom' => $comment->getUser()->getNom(),
                    'avatar' => $comment->getUser()->getImageUrl() 
                        ? '/uploads/users/' . $comment->getUser()->getImageUrl()
                        : '/img/screen/user.png'
                ],
                'tokens' => [
                    'edit' => $this->generateToken('edit-comment' . $comment->getId()),
                    'delete' => $this->generateToken('delete' . $comment->getId())
                ],
                'toxicityScore' => $toxicityScore
            ];

            return new JsonResponse($response, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'enregistrement du commentaire: ' . $e->getMessage());
            $this->logger->error('Stack trace: ' . $e->getTraceAsString());
            return new JsonResponse(
                [
                    'error' => 'Une erreur est survenue lors de l\'enregistrement du commentaire',
                    'details' => $e->getMessage()
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function generateToken(string $intention): string
    {
        return $this->container->get('security.csrf.token_manager')->getToken($intention)->getValue();
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
        if ($comment->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce commentaire.');
        }

        if ($request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true);
            
            if (!$this->isCsrfTokenValid('edit-comment' . $comment->getId(), $data['_token'])) {
                return new JsonResponse(['error' => 'Token CSRF invalide'], Response::HTTP_BAD_REQUEST);
            }

            $content = trim($data['content']);
            if (strlen($content) < 2) {
                return new JsonResponse(['error' => 'Le commentaire est trop court'], Response::HTTP_BAD_REQUEST);
            }

            $comment->setContent($content);
            $comment->setCreatedAt(new \DateTime());
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('d M Y à H:i'),
                'id' => $comment->getId()
            ]);
        }

        // Pour les requêtes non-AJAX (formulaire classique)
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTime());
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
            return new JsonResponse([
                'success' => false,
                'message' => 'Commentaire introuvable'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($comment->getUser() !== $this->getUser()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à supprimer ce commentaire'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Commentaire supprimé avec succès'
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Token CSRF invalide'
        ], Response::HTTP_BAD_REQUEST);
    }
























}