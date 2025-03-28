<?php

namespace App\Controller;

use App\Entity\Activité; // Changé de 'activité' à 'Activite'
use App\Enum\ActivityType;
use App\Repository\ActivityRepository; 

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

class ActivityController extends AbstractController
{
    #[Route('/activity/new', name: 'app_activity_new')]
    public function new(): Response
    {
        return $this->render('activity/new.html.twig', [
            'page_title' => 'Add New Activity',
            'activity_types' => ActivityType::cases()
        ]);
    }

    #[Route('/activity/add', name: 'app_activity_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        // Vérification CSRF
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('create_activity', $submittedToken)) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('app_activity_new');
        }

        $activity = new Activité();
        $activity->setNom($request->request->get('nom'));
        $activity->setDescription($request->request->get('description'));

        try {
            $activity->setType(ActivityType::from($request->request->get('type')));
        } catch (\ValueError $e) {
            $this->addFlash('error', 'Invalid activity type selected');
            return $this->redirectToRoute('app_activity_new');
        }

        // Gestion de l'image
        $imageFile = $request->files->get('activityImage');
        if ($imageFile) {
            try {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                
                // Chemin par défaut si le paramètre n'existe pas
                $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/activities';
                
                // Création du dossier s'il n'existe pas
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $imageFile->move(
                    $uploadDir,
                    $newFilename
                );
                $activity->setUrl('/uploads/activities/'.$newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Error uploading image');
                return $this->redirectToRoute('app_activity_new');
            }
        }

        $entityManager->persist($activity);
        $entityManager->flush();

        $this->addFlash('success', 'Activity created successfully!');
        return $this->redirectToRoute('app_activity_new');
    }
    #[Route('/activity/edit/{id}', name: 'app_activity_edit')]
    public function edit(int $id, ActivityRepository $repository): Response
{
    $activity = $repository->find($id);
    
    if (!$activity) {
        $this->addFlash('error', 'Activity not found');
        return $this->redirectToRoute('app_activity_index');
    }

    return $this->render('activity/edit.html.twig', [
        'page_title' => 'Edit Activity',
        'activity' => $activity,
        'activity_types' => ActivityType::cases()
    ]);
}

    
    #[Route('/activity/delete/{id}', name: 'app_activity_delete', methods: ['POST'])]
public function delete(
    Request $request,
    EntityManagerInterface $entityManager,
    int $id
): Response {
    $activity = $entityManager->getRepository(Activité::class)->find($id);
    
    if (!$activity) {
        $this->addFlash('error', 'Activity not found');
        return $this->redirectToRoute('app_activity_index');
    }

    if (!$this->isCsrfTokenValid('delete'.$activity->getId(), $request->request->get('_token'))) {
        $this->addFlash('error', 'Invalid CSRF token');
        return $this->redirectToRoute('app_activity_index');
    }

    // Remove associated image
    if ($activity->getUrl()) {
        $imagePath = $this->getParameter('kernel.project_dir').'/public'.$activity->getUrl();
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    $entityManager->remove($activity);
    $entityManager->flush();

    $this->addFlash('success', 'Activity deleted successfully!');
    return $this->redirectToRoute('app_activity_index');
}
#[Route('/activity', name: 'app_activity_index')]
public function activity(ActivityRepository $activityRepository): Response
       { return $this->render('activity/index.html.twig', [
          'page_title' => 'Activity Dashboard',
          'activities' => $activityRepository->findAll(),
          'stats' => [
                'visitors' => 1294,
                'subscribers' => 1303,
                'sales' => 1345,
                'orders' => 576
            ]
      ]);
  }
  #[Route('/activity/update/{id}', name: 'app_activity_update', methods: ['POST'])]
public function update(
    Request $request, 
    int $id, 
    EntityManagerInterface $entityManager,
    SluggerInterface $slugger
): Response {
    $activity = $entityManager->getRepository(Activité::class)->find($id);
    
    if (!$activity) {
        $this->addFlash('error', 'Activity not found');
        return $this->redirectToRoute('app_activity_index');
    }

    if (!$this->isCsrfTokenValid('update'.$activity->getId(), $request->request->get('_token'))) {
        $this->addFlash('error', 'Invalid CSRF token');
        return $this->redirectToRoute('app_activity_edit', ['id' => $id]);
    }

    // Gestion de la suppression d'image
    if ($request->request->has('remove_image')) {
        $this->removeImage($activity->getUrl());
        $activity->setUrl(null);
    }

    // Traitement des données
    $activity->setNom($request->request->get('nom'));
    $activity->setDescription($request->request->get('description'));
    $activity->setType(ActivityType::from($request->request->get('type')));

    // Gestion de la nouvelle image
    $imageFile = $request->files->get('activityImage');
    if ($imageFile) {
        // Supprimer l'ancienne image si elle existe
        if ($activity->getUrl()) {
            $this->removeImage($activity->getUrl());
        }
        
        $newFilename = $this->uploadImage($imageFile, $slugger);
        $activity->setUrl('/uploads/activities/'.$newFilename);
    }

    $entityManager->flush();

    $this->addFlash('success', 'Activity updated successfully!');
    return $this->redirectToRoute('app_activity_index');
}

}