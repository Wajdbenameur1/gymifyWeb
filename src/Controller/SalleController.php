<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Form\SalleType;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin/salle')]
final class SalleController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'admin_salle_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(SalleRepository $salleRepository): Response
    {
        return $this->render('salle/index.html.twig', [
            'salles' => $salleRepository->findAll(),
            'page_title' => 'Gym Management',
        ]);
    }

    #[Route('/new', name: 'admin_salle_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request): Response
    {
        $salle = new Salle();
        $form = $this->createForm(SalleType::class, $salle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $salle->setUrlPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image: ' . $e->getMessage());
                    return $this->redirectToRoute('admin_salle_new');
                }
            }

            $this->entityManager->persist($salle);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Gym room created successfully!');
            return $this->redirectToRoute('admin_salle_index');
        }

        return $this->render('salle/new.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Add New Gym Room',
        ]);
    }

    #[Route('/{id}', name: 'admin_salle_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Salle $salle): Response
    {
        return $this->render('salle/show.html.twig', [
            'salle' => $salle,
            'page_title' => 'Gym Room: ' . $salle->getNom(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_salle_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Salle $salle): Response
    {
        $form = $this->createForm(SalleType::class, $salle, [
            'current_salle_id' => $salle->getId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    // Delete old image if it exists
                    if ($salle->getUrlPhoto()) {
                        $oldImagePath = $this->getParameter('images_directory') . '/' . $salle->getUrlPhoto();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $salle->setUrlPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image: ' . $e->getMessage());
                    return $this->redirectToRoute('admin_salle_edit', ['id' => $salle->getId()]);
                }
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Gym room updated successfully!');
            return $this->redirectToRoute('admin_salle_index');
        }

        return $this->render('salle/edit.html.twig', [
            'salle' => $salle,
            'form' => $form->createView(),
            'page_title' => 'Edit Gym Room: ' . $salle->getNom(),
        ]);
    }

    #[Route('/{id}', name: 'admin_salle_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Salle $salle): Response
    {
        if ($this->isCsrfTokenValid('delete' . $salle->getId(), $request->request->get('_token'))) {
            // Delete image if it exists
            if ($salle->getUrlPhoto()) {
                $imagePath = $this->getParameter('images_directory') . '/' . $salle->getUrlPhoto();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $this->entityManager->remove($salle);
            $this->entityManager->flush();
            $this->addFlash('success', 'Gym room deleted successfully!');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('admin_salle_index');
    }
}