<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Repository\ReclamationRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/reclamations')]
#[IsGranted('ROLE_ADMIN')]
class ReclamationController extends AbstractController
{
    private $entityManager;
    private $reclamationRepository;
    private $reponseRepository;

    public function __construct(EntityManagerInterface $entityManager, ReclamationRepository $reclamationRepository, ReponseRepository $reponseRepository)
    {
        $this->entityManager = $entityManager;
        $this->reclamationRepository = $reclamationRepository;
        $this->reponseRepository = $reponseRepository;
    }

    #[Route('/', name: 'admin_reclamations_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $reclamations = $this->reclamationRepository->findAll();
        $reponses = $this->reponseRepository->findAll();

        // Form for adding a response
        $form = $this->createFormBuilder()
            ->add('message', TextareaType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'Écrivez votre réponse ici...', 'class' => 'w-full p-2 border rounded-lg'],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedReclamationIds = $request->request->all('selected_reclamations') ?? [];
            $message = $form->get('message')->getData();

            if (empty($selectedReclamationIds)) {
                $this->addFlash('warning', 'Veuillez sélectionner au moins une réclamation.');
                return $this->redirectToRoute('admin_reclamations_index');
            }

            if (empty($message)) {
                $this->addFlash('warning', 'Le message de réponse ne peut pas être vide.');
                return $this->redirectToRoute('admin_reclamations_index');
            }

            foreach ($selectedReclamationIds as $reclamationId) {
                $reclamation = $this->reclamationRepository->find($reclamationId);
                if ($reclamation) {
                    $reponse = new Reponse();
                    $reponse->setReclamation($reclamation);
                    $reponse->setUser($this->getUser());
                    $reponse->setMessage($message);
                    $reponse->setDateReponse(new \DateTime());
                    $reclamation->setStatut('Traitée');

                    $this->entityManager->persist($reponse);
                }
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Réponses envoyées avec succès !');

            return $this->redirectToRoute('admin_reclamations_index');
        }

        return $this->render('admin/reclamations.html.twig', [
            'reclamations' => $reclamations,
            'reponses' => $reponses,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete-reponses', name: 'admin_reclamations_delete_reponses', methods: ['POST'])]
    public function deleteReponses(Request $request): Response
    {
        $selectedReponseIds = $request->request->all('selected_reponses') ?? [];

        if (empty($selectedReponseIds)) {
            $this->addFlash('warning', 'Veuillez sélectionner au moins une réponse à supprimer.');
            return $this->redirectToRoute('admin_reclamations_index');
        }

        foreach ($selectedReponseIds as $reponseId) {
            $reponse = $this->reponseRepository->find($reponseId);
            if ($reponse) {
                $this->entityManager->remove($reponse);
            }
        }

        $this->entityManager->flush();
        $this->addFlash('success', 'Réponses supprimées avec succès !');

        return $this->redirectToRoute('admin_reclamations_index');
    }
}