<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Form\EquipeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class EquipeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/equipe', name: 'app_equipe_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchTerm = $request->query->get('search', '');

        $qb = $this->entityManager->getRepository(Equipe::class)->createQueryBuilder('e');

        if ($searchTerm) {
            $qb->where('LOWER(e.nom) LIKE LOWER(:search)')
               ->orWhere('LOWER(e.niveau) LIKE LOWER(:search)')
               ->setParameter('search', '%'.$searchTerm.'%');
            $equipes = $qb->getQuery()->getResult();
        } else {
            $equipes = $this->entityManager->getRepository(Equipe::class)->findAll();
        }

        return $this->render('equipe/index.html.twig', [
            'equipes' => $equipes,
            'page_title' => 'List of Teams',
        ]);
    }

    #[Route('/equipe/new', name: 'app_equipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $equipe->setImageUrl('/uploads/images/' . $newFilename);
            }

            $this->entityManager->persist($equipe);
            $this->entityManager->flush();

            $this->addFlash('success', 'Team added successfully!');
            return $this->redirectToRoute('app_equipe_index');
        }

        return $this->render('equipe/new.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Add New Team',
        ]);
    }

    #[Route('/equipe/{id}', name: 'app_equipe_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(?Equipe $equipe, int $id): Response
    {
        if (!$equipe) {
            throw new NotFoundHttpException("Team with ID $id not found.");
        }

        return $this->render('equipe/show.html.twig', [
            'equipe' => $equipe,
            'page_title' => 'Team Details - ' . $equipe->getNom(),
        ]);
    }

    #[Route('/equipe/{id}/edit', name: 'app_equipe_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipe $equipe, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EquipeType::class, $equipe, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_equipe_edit', ['id' => $equipe->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            $removeImage = $request->request->get('remove_image');

            if ($removeImage && $equipe->getImageUrl()) {
                $oldImagePath = $this->getParameter('images_directory') . '/' . basename($equipe->getImageUrl());
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                $equipe->setImageUrl(null);
            }

            if ($imageFile) {
                if ($equipe->getImageUrl()) {
                    $oldImagePath = $this->getParameter('images_directory') . '/' . basename($equipe->getImageUrl());
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $equipe->setImageUrl('/uploads/images/' . $newFilename);
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Team updated successfully!');
            return $this->redirectToRoute('app_equipe_index');
        }

        return $this->render('equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form->createView(),
            'page_title' => 'Edit Team',
        ]);
    }

    #[Route('/equipe/{id}/delete', name: 'app_equipe_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Equipe $equipe): Response
    {
        if ($this->isCsrfTokenValid('delete' . $equipe->getId(), $request->request->get('_token'))) {
            if ($equipe->getImageUrl()) {
                $imagePath = $this->getParameter('images_directory') . '/' . basename($equipe->getImageUrl());
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $this->entityManager->remove($equipe);
            $this->entityManager->flush();
            $this->addFlash('success', 'Team deleted successfully!');
        }

        return $this->redirectToRoute('app_equipe_index');
    }
}