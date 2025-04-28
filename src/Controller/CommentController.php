<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Service\AblyService;
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
use Doctrine\DBAL\Exception as DBALException;


#[Route('/comment')]
final class CommentController extends AbstractController
{
    public function __construct(
        private readonly PerspectiveApiService $perspectiveApiService,
        private readonly EmailJSService $emailJSService,
        private readonly LoggerInterface $logger,
        private readonly AblyService $ablyService
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
        
        try {
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

            // Analyse de la toxicité du commentaire
            $this->logger->info('Début de l\'analyse de toxicité pour le commentaire: ' . $content);
            $toxicityScore = $this->perspectiveApiService->analyzeCommentToxicity($content);
            $this->logger->info('Score de toxicité reçu: ' . $toxicityScore);
            
            $user = $this->getUser();
            if (!$user) {
                $this->logger->error('Aucun utilisateur connecté');
                return new JsonResponse(['error' => 'Vous devez être connecté pour poster un commentaire'], Response::HTTP_UNAUTHORIZED);
            }

            // Contenu à enregistrer dans la base de données
            $storedContent = $content;

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

            // Commentaire sensible - on encode le contenu original avec un préfixe spécial
            if ($toxicityScore > 0.4) {
                $this->logger->info('Commentaire sensible (0.4 < score <= 0.6), encodage du contenu original');
                
                // On encode le contenu original avec un préfixe spécial
                // Format: [SENSITIVE]{contenu original}
                $storedContent = '[SENSITIVE]' . $content;
                
                // Envoi d'email de notification
                $this->emailJSService->sendEmail(
                    $user->getEmail(),
                    '⚠️ Votre commentaire a été modéré',
                    'Votre commentaire contient un contenu sensible. Veuillez le modifier si nécessaire.'
                );
            } else {
                $this->logger->info('Commentaire acceptable (score <= 0.4)');
            }

            // Création du commentaire
            $commentId = null;
            $comment = null;
            $date = new \DateTime();
            $insertSuccessful = false;
            
            // Première tentative: avec SQL direct (inversion pour fiabilité)
            try {
                $this->logger->info('Tentative d\'enregistrement via SQL direct');
                $userId = $user->getId();
                $postIdValue = $post->getId();
                $commentId = $this->directSqlInsertComment($em, $storedContent, $postIdValue, $userId, $date);
                
                if ($commentId) {
                    $insertSuccessful = true;
                    $this->logger->info('Commentaire enregistré avec succès via SQL direct, ID: ' . $commentId);
                    
                    // Créer un objet Comment pour l'affichage
                    $comment = new Comment();
                    $comment->setContent($storedContent)
                        ->setPost($post)
                        ->setUser($user)
                        ->setCreatedAt($date);
                    
                    // Assignation manuelle de l'ID
                    $reflection = new \ReflectionClass($comment);
                    $property = $reflection->getProperty('id');
                    $property->setAccessible(true);
                    $property->setValue($comment, $commentId);
                }
            } catch (\Exception $sqlException) {
                $this->logger->error('Échec de l\'enregistrement via SQL direct: ' . $sqlException->getMessage());
            }
            
            // Seconde tentative: avec Doctrine ORM si SQL direct a échoué
            if (!$insertSuccessful) {
                try {
                    $this->logger->info('Tentative d\'enregistrement via ORM');
                    $comment = new Comment();
                    $comment->setContent($storedContent)
                        ->setPost($post)
                        ->setUser($user)
                        ->setCreatedAt($date);
                    
                    $em->persist($comment);
                    $em->flush();
                    $commentId = $comment->getId();
                    $insertSuccessful = true;
                    $this->logger->info('Commentaire enregistré avec succès via ORM, ID: ' . $commentId);
                } catch (\Exception $ormException) {
                    $this->logger->error('Échec de l\'enregistrement via ORM: ' . $ormException->getMessage());
                    throw new \Exception('Impossible d\'enregistrer le commentaire en base de données: ' . $ormException->getMessage());
                }
            }

            if (!$insertSuccessful || !$commentId) {
                throw new \Exception('Impossible d\'enregistrer le commentaire en base de données');
            }

            // Pour la réponse JSON, nous utilisons soit le contenu sensible, soit le contenu original
            $displayContent = $toxicityScore > 0.4 ? 
                '⚠️ Votre commentaire contient un contenu sensible. Veuillez le modifier si nécessaire.' : 
                $content;

            // Préparation de la réponse
            $response = [
                'id' => $commentId,
                'content' => $displayContent,
                'createdAt' => $date->format('d M Y à H:i'),
                'user' => [
                    'nom' => $user->getNom(),
                    'avatar' => $user->getImageUrl() 
                        ? '/uploads/users/' . $user->getImageUrl()
                        : '/img/screen/user.png'
                ],
                'tokens' => [
                    'edit' => $this->generateToken('edit-comment' . $commentId),
                    'delete' => $this->generateToken('delete' . $commentId)
                ],
                'toxicityScore' => $toxicityScore,
                'originalContent' => $toxicityScore > 0.4 ? $content : null // Pour le frontend
            ];
            
            // Send real-time notification via Ably for the new comment
            $this->ablyService->publishNewComment([
                'id' => $commentId,
                'content' => $displayContent,
                'postId' => $post->getId(),
                'postTitle' => $post->getTitle(),
                'createdAt' => $date->format('d M Y à H:i'),
                'user' => [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'avatar' => $user->getImageUrl() 
                        ? '/uploads/users/' . $user->getImageUrl()
                        : '/img/screen/user.png'
                ]
            ]);

            return new JsonResponse($response, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            $this->logger->error('Erreur non capturée: ' . $e->getMessage());
            $this->logger->error('Trace complète: ' . $e->getTraceAsString());
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

    /**
     * Effectue une insertion directe d'un commentaire via SQL
     */
    private function directSqlInsertComment(
        EntityManagerInterface $em, 
        string $content, 
        int $postId, 
        int $userId, 
        \DateTime $date
    ): ?int {
        try {
            $conn = $em->getConnection();
            $dateStr = $date->format('Y-m-d H:i:s');
            
            $stmt = $conn->prepare('
                INSERT INTO comment (content, postId, id_User, createdAt) 
                VALUES (:content, :postId, :userId, :createdAt)
            ');
            
            $stmt->executeQuery([
                'content' => $content,
                'postId' => $postId,
                'userId' => $userId,
                'createdAt' => $dateStr
            ]);
            
            $commentId = $conn->lastInsertId();
            $this->logger->info('SQL direct: Commentaire inséré avec ID ' . $commentId);
            return $commentId ? (int)$commentId : null;
        } catch (\Exception $e) {
            $this->logger->error('SQL direct: Erreur lors de l\'insertion: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Effectue une mise à jour directe d'un commentaire via SQL
     */
    private function directSqlUpdateComment(
        EntityManagerInterface $em, 
        int $commentId, 
        string $content, 
        \DateTime $date
    ): bool {
        try {
            $conn = $em->getConnection();
            $dateStr = $date->format('Y-m-d H:i:s');
            
            $stmt = $conn->prepare('
                UPDATE comment 
                SET content = :content, 
                    createdAt = :createdAt 
                WHERE id = :commentId
            ');
            
            $stmt->executeQuery([
                'content' => $content,
                'createdAt' => $dateStr,
                'commentId' => $commentId
            ]);
            
            $this->logger->info('SQL direct: Commentaire mis à jour avec ID ' . $commentId);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('SQL direct: Erreur lors de la mise à jour: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Effectue une suppression directe d'un commentaire via SQL
     */
    private function directSqlDeleteComment(
        EntityManagerInterface $em, 
        int $commentId
    ): bool {
        try {
            $conn = $em->getConnection();
            
            $stmt = $conn->prepare('
                DELETE FROM comment 
                WHERE id = :commentId
            ');
            
            $stmt->executeQuery([
                'commentId' => $commentId
            ]);
            
            $this->logger->info('SQL direct: Commentaire supprimé avec ID ' . $commentId);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('SQL direct: Erreur lors de la suppression: ' . $e->getMessage());
            return false;
        }
    }
























    #[Route('/{id}', name: 'app_comment_show', methods: ['GET'])]
    public function show(Comment $comment): Response
    {
        // Vérifie si c'est un commentaire sensible
        $content = $comment->getContent();
        $isSensitive = strpos($content, '[SENSITIVE]') === 0;
        
        // Si c'est un commentaire sensible, récupère le contenu original
        if ($isSensitive) {
            $originalContent = substr($content, 11); // Enlever le préfixe '[SENSITIVE]'
            // On conserve le contenu original pour l'affichage
            $comment->setContent($originalContent);
        }
        
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
            'isSensitive' => $isSensitive
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

            // Analyse de la toxicité du nouveau contenu
            $toxicityScore = $this->perspectiveApiService->analyzeCommentToxicity($content);
            $this->logger->info('Score de toxicité pour l\'édition du commentaire: ' . $toxicityScore);
            
            // Contenu à stocker en base
            $storedContent = $content;
            
            // Si le score de toxicité est élevé mais acceptable
            if ($toxicityScore > 0.4 && $toxicityScore <= 0.6) {
                // On marque le commentaire comme sensible
                $storedContent = '[SENSITIVE]' . $content;
                $displayContent = '⚠️ Votre commentaire contient un contenu sensible. Veuillez le modifier si nécessaire.';
            } else if ($toxicityScore > 0.6) {
                // Si trop toxique, on rejette la modification
                return new JsonResponse([
                    'error' => 'Votre commentaire contient un contenu trop toxique',
                    'toxicityScore' => $toxicityScore
                ], Response::HTTP_BAD_REQUEST);
            } else {
                // Commentaire normal
                $displayContent = $content;
            }

            // Mise à jour du commentaire
            $commentId = $comment->getId();
            $now = new \DateTime();
            $updateSuccess = false;
            
            // Première tentative: avec Doctrine ORM
            try {
                $this->logger->info('Tentative de mise à jour via ORM');
                $comment->setContent($storedContent);
                $comment->setCreatedAt($now);
                
                $entityManager->persist($comment);
                $entityManager->flush();
                $updateSuccess = true;
                $this->logger->info('Commentaire mis à jour avec succès via ORM, ID: ' . $commentId);
            } catch (\Exception $ormException) {
                $this->logger->warning('Échec de la mise à jour via ORM: ' . $ormException->getMessage());
                
                // Seconde tentative: avec SQL direct
                $updateSuccess = $this->directSqlUpdateComment($entityManager, $commentId, $storedContent, $now);
                
                if (!$updateSuccess) {
                    throw new \Exception('Impossible de mettre à jour le commentaire en base de données');
                }
            }

            return new JsonResponse([
                'success' => true,
                'content' => $displayContent,
                'originalContent' => $toxicityScore > 0.4 && $toxicityScore <= 0.6 ? $content : null,
                'createdAt' => $now->format('d M Y à H:i'),
                'id' => $commentId,
                'toxicityScore' => $toxicityScore
            ]);
        }

        // Pour les requêtes non-AJAX (formulaire classique)
        // Récupérer le contenu réel si c'est un commentaire sensible
        $originalContent = $comment->getContent();
        $isSensitive = strpos($originalContent, '[SENSITIVE]') === 0;
        
        if ($isSensitive) {
            $realContent = substr($originalContent, 11); // Enlever le préfixe '[SENSITIVE]'
            $comment->setContent($realContent); // Pour l'affichage dans le formulaire
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le nouveau contenu
            $content = $comment->getContent();
            
            // Analyse de la toxicité
            $toxicityScore = $this->perspectiveApiService->analyzeCommentToxicity($content);
            
            // Gestion de la sensibilité
            if ($toxicityScore > 0.4 && $toxicityScore <= 0.6) {
                // Marquer comme sensible
                $comment->setContent('[SENSITIVE]' . $content);
            } else if ($toxicityScore > 0.6) {
                // Si trop toxique, on annule l'édition
                $this->addFlash('error', 'Votre commentaire contient un contenu trop toxique');
                return $this->render('comment/edit.html.twig', [
                    'comment' => $comment,
                    'form' => $form->createView(),
                ]);
            }
            
            $now = new \DateTime();
            $comment->setCreatedAt($now);
            $updateSuccess = false;
            
            // Première tentative: avec Doctrine ORM
            try {
                $entityManager->persist($comment);
                $entityManager->flush();
                $updateSuccess = true;
                $this->addFlash('success', 'Commentaire modifié avec succès');
            } catch (\Exception $ormException) {
                $this->logger->warning('Échec de la mise à jour formulaire via ORM: ' . $ormException->getMessage());
                
                // Seconde tentative: avec SQL direct
                $updateSuccess = $this->directSqlUpdateComment(
                    $entityManager, 
                    $comment->getId(), 
                    $comment->getContent(), 
                    $now
                );
                
                if ($updateSuccess) {
                    $this->addFlash('success', 'Commentaire modifié avec succès');
                } else {
                    $this->addFlash('error', 'Erreur lors de l\'enregistrement des modifications');
                    return $this->render('comment/edit.html.twig', [
                        'comment' => $comment,
                        'form' => $form->createView(),
                    ]);
                }
            }
            
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
            $deleteSuccess = false;
            
            // Capture les données avant suppression pour la notification
            $postId = $comment->getPost()->getId();
            $postTitle = $comment->getPost()->getTitle();
            $userId = $comment->getUser()->getId();
            $userName = $comment->getUser()->getNom();
            
            // Première tentative: avec Doctrine ORM
            try {
                $this->logger->info('Tentative de suppression via ORM');
                $entityManager->remove($comment);
                $entityManager->flush();
                $deleteSuccess = true;
                $this->logger->info('Commentaire supprimé avec succès via ORM, ID: ' . $id);
            } catch (\Exception $ormException) {
                $this->logger->warning('Échec de la suppression via ORM: ' . $ormException->getMessage());
                
                // Seconde tentative: avec SQL direct
                $deleteSuccess = $this->directSqlDeleteComment($entityManager, $id);
                
                if (!$deleteSuccess) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Impossible de supprimer le commentaire en base de données'
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
            
            // Envoie une notification Ably pour la suppression du commentaire
            $this->ablyService->publishEvent('comments', 'delete-comment', [
                'commentId' => $id,
                'postId' => $postId,
                'postTitle' => $postTitle,
                'user' => [
                    'id' => $userId,
                    'nom' => $userName
                ]
            ]);

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