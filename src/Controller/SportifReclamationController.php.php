<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Repository\ReclamationRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sportif/reclamations')]
#[IsGranted('ROLE_USER')]
class SportifReclamationController extends AbstractController
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

    #[Route('/', name: 'sportif_reclamations_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status', 'Tous');
        $reclamations = $this->reclamationRepository->findByUserAndStatus($this->getUser(), $status === 'Tous' ? null : $status);

        $reclamation = new Reclamation();
        $form = $this->createFormBuilder($reclamation)
            ->add('sujet', TextType::class, ['attr' => ['placeholder' => 'Sujet', 'class' => 'w-full p-2 border rounded-lg']])
            ->add('description', TextareaType::class, ['attr' => ['placeholder' => 'Décrivez votre problème...', 'class' => 'w-full p-2 border rounded-lg']])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setUser($this->getUser());
            $reclamation->setDateCreation(new \DateTime());
            $reclamation->setStatut('En attente');

            $this->entityManager->persist($reclamation);
            $this->entityManager->flush();

            $this->addFlash('success', 'Réclamation envoyée avec succès !');
            return $this->redirectToRoute('sportif_reclamations_index');
        }

        return $this->render('sportif/reclamations.html.twig', [
            'reclamations' => $reclamations,
            'form' => $form->createView(),
            'selected_status' => $status,
        ]);
    }

    #[Route('/delete/{id}', name: 'sportif_reclamations_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($reclamation);
            $this->entityManager->flush();
            $this->addFlash('success', 'Réclamation supprimée !');
        }

        return $this->redirectToRoute('sportif_reclamations_index');
    }
}