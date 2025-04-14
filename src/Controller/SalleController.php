<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Form\SalleType;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin/salle')]
class SalleController extends AbstractController
{
    #[Route('/', name: 'admin_salle_index', methods: ['GET'])]
    public function index(SalleRepository $salleRepository): Response
    {
        $salles = $salleRepository->findAll();
        return $this->render('salle/index.html.twig', [
            'page_title' => '🏋️‍♀️ Nos Salles de Sport',
            'salles' => $salles,
        ]);
    }

    #[Route('/new', name: 'admin_salle_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $salle = new Salle();
        $form = $this->createForm(SalleType::class, $salle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                    return $this->redirectToRoute('admin_salle_new');
                }
                $salle->setUrlPhoto($newFilename);
            }

            $em->persist($salle);
            $em->flush();

            $this->addFlash('success', 'Salle créée avec succès !');
            return $this->redirectToRoute('admin_salle_index');
        }

        return $this->render('salle/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_salle_show', methods: ['GET'])]
    public function show(Salle $salle): Response
    {
        return $this->render('salle/show.html.twig', [
            'salle' => $salle,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_salle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Salle $salle, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SalleType::class, $salle, [
            'current_salle_id' => $salle->getId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('salles_directory'),
                    $newFilename
                );
                // Supprimer l'ancienne image si elle existe
                if ($salle->getImage()) {
                    $oldImage = $this->getParameter('salles_directory').'/'.$salle->getImage();
                    if (file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }
                $salle->setImage($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'La salle a été modifiée avec succès');
            return $this->redirectToRoute('admin_salle_index');
        }

        return $this->render('salle/edit.html.twig', [
            'form' => $form->createView(),
            'salle' => $salle,
        ]);
    }

    #[Route('/{id}', name: 'admin_salle_delete', methods: ['POST'])]
    public function delete(Request $request, Salle $salle, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $salle->getId(), $request->request->get('_token'))) {
            // Supprimer l'image si elle existe
            if ($salle->getUrlPhoto()) {
                $imagePath = $this->getParameter('images_directory') . '/' . $salle->getUrlPhoto();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $em->remove($salle);
            $em->flush();

            $this->addFlash('success', 'Salle supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_salle_index');
    }
}