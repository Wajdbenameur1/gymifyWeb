<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Reactions;
use App\Service\AblyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ReactionController extends AbstractController
{
    private AblyService $ablyService;
    
    public function __construct(AblyService $ablyService)
    {
        $this->ablyService = $ablyService;
    }
    
    #[Route('/reaction/{post}', name: 'app_reaction_toggle', methods: ['POST'])]
    public function toggle(Post $post, Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Récupération de l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        // Validation du type envoyé
        $type = $request->request->get('type');
        if (!isset(Reactions::TYPES[$type])) {
            return $this->json(['error' => 'Invalid type'], 400);
        }

        // Vérifier si l'utilisateur a déjà réagi à ce post
        $existing = $post->getReactionByUser($user);
        $isNewReaction = false;
        $isChangedReaction = false;
        $oldReactionType = null;
        
        if ($existing) {
            if ($existing->getType() === $type) {
                // Même réaction => suppression
                $em->remove($existing);
                $em->flush();
                $currentUserReaction = null;
            } else {
                // Modification du type de réaction
                $oldReactionType = $existing->getType();
                $existing->setType($type);
                $em->flush();
                $currentUserReaction = $type;
                $isChangedReaction = true;
            }
        } else {
            // Nouvelle réaction
            $reaction = new Reactions();
            $reaction->setUser($user)
                     ->setPost($post)
                     ->setType($type);
            $em->persist($reaction);
            $em->flush();
            $currentUserReaction = $type;
            $isNewReaction = true;
        }

        // Calcul des totaux par type
        $counts = $post->getReactionsCountByType();
        
        // Only send notification for new or changed reactions (not for removals)
        if ($isNewReaction || $isChangedReaction) {
            $this->ablyService->publishNewReaction([
                'postId' => $post->getId(),
                'postTitle' => $post->getTitle(),
                'reactionType' => $type,
                'reactionEmoji' => Reactions::TYPES[$type],
                'isNew' => $isNewReaction,
                'isChanged' => $isChangedReaction,
                'oldType' => $oldReactionType,
                'user' => [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                ],
                'totalReactions' => array_sum($counts),
                'counts' => $counts
            ]);
        }

        return $this->json([
            'counts' => $counts,
            'userReaction' => $currentUserReaction,
        ]);
    }
}
