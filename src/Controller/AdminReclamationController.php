<?php
namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\ReclamationRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/admin/reclamation')]
class AdminReclamationController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    #[Route('/', name: 'app_admin_reclamation_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ReclamationRepository $reclamationRepository,
        ReponseRepository $reponseRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Fetch all reclamations and responses
        $reclamations = $reclamationRepository->findAll();
        $reponses = $reponseRepository->findAll();

        // Create the response form
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->logger->info('Formulaire de réponse soumis');
            if (!$form->isValid()) {
                $errors = $form->getErrors(true);
                $this->logger->error('Formulaire invalide', ['errors' => (string) $errors]);
                $this->addFlash('error', 'Formulaire invalide. Vérifiez le champ de réponse.');
            } else {
                // Use request->get() to safely handle array input
                $selectedReclamations = $request->get('selected_reclamations', []);
                if (!is_array($selectedReclamations)) {
                    $selectedReclamations = [];
                }
                $this->logger->info('Réclamations sélectionnées', ['selected' => $selectedReclamations]);

                if (empty($selectedReclamations)) {
                    $this->addFlash('warning', 'Veuillez sélectionner au moins une réclamation.');
                } else {
                    $admin = $this->getUser();
                    if (!$admin) {
                        $this->logger->error('Utilisateur admin non connecté');
                        $this->addFlash('error', 'Vous devez être connecté en tant qu\'admin.');
                        return $this->redirectToRoute('app_admin_reclamation_index');
                    }

                    foreach ($selectedReclamations as $reclamationId) {
                        $reclamation = $reclamationRepository->find($reclamationId);
                        if (!$reclamation) {
                            $this->logger->warning('Réclamation introuvable', ['id' => $reclamationId]);
                            $this->addFlash('error', 'Réclamation #' . $reclamationId . ' introuvable.');
                            continue;
                        }

                        // Create a new Reponse entity
                        $newReponse = new Reponse();
                        $newReponse->setMessage($reponse->getMessage());
                        $newReponse->setDateReponse(new \DateTime());
                        $newReponse->setAdmin($admin);
                        $newReponse->setReclamation($reclamation);

                        // Update reclamation status
                        $reclamation->setStatut('Traitée');

                        // Persist entities
                        $entityManager->persist($newReponse);
                        $entityManager->persist($reclamation);
                    }

                    try {
                        $entityManager->flush();
                        $this->addFlash('success', 'Réponses envoyées avec succès.');
                        $this->logger->info('Réponses enregistrées avec succès');
                    } catch (\Exception $e) {
                        $this->logger->error('Erreur lors de l\'enregistrement des réponses', ['exception' => $e->getMessage()]);
                        $this->addFlash('error', 'Erreur lors de l\'envoi des réponses : ' . $e->getMessage());
                    }
                }
            }
            return $this->redirectToRoute('app_admin_reclamation_index');
        }

        return $this->render('admin_reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'reponses' => $reponses,
            'form' => $form->createView(),
            'page_title' => 'Gestion des Réclamations',
        ]);
    }

    #[Route('/reponse/delete', name: 'app_admin_reponse_delete', methods: ['POST'])]
    public function deleteReponse(
        Request $request,
        ReponseRepository $reponseRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Use request->get() to safely handle array input
        $selectedReponses = $request->get('selected_reponses', []);
        if (!is_array($selectedReponses)) {
            $selectedReponses = [];
        }
        $this->logger->info('Suppression des réponses', ['selected' => $selectedReponses]);

        if (empty($selectedReponses)) {
            $this->addFlash('warning', 'Veuillez sélectionner au moins une réponse à supprimer.');
        } else {
            foreach ($selectedReponses as $reponseId) {
                $reponse = $reponseRepository->find($reponseId);
                if ($reponse) {
                    $entityManager->remove($reponse);
                }
            }
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Réponses supprimées avec succès.');
                $this->logger->info('Réponses supprimées avec succès');
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la suppression des réponses', ['exception' => $e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la suppression des réponses : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_admin_reclamation_index');
    }
}