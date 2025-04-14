<?php

namespace App\Controller;

use App\Entity\Activité; // Changé de 'activité' à 'Activite'
use App\Enum\ActivityType;
use App\Repository\ActivityRepository; 
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
      $activity = new Activite();
       $form = $this->createForm(ActivityFormType::class, $activity);

        return $this->render('activity/new.html.twig', [
            'page_title' => 'Add New Activity',
            'form' => $form->createView()

        ]);
    }

    
    #[Route('/activity/add', name: 'app_activity_create', methods: ['POST'])]
public function create(
    Request $request,
    EntityManagerInterface $entityManager,
    SluggerInterface $slugger
): Response {
    $activity = new Activite();
    $form = $this->createForm(ActivityType::class, $activity);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion de l'image
        $imageFile = $form->get('imageFile')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
            
            $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/activities';
            if (!file_exists($uploadDir)) {
              mkdir($uploadDir, 0777, true);
          }
          
          $imageFile->move($uploadDir, $newFilename);
          $activity->setUrl('/uploads/activities/'.$newFilename);
      }

      $entityManager->persist($activity);
      $entityManager->flush();

      $this->addFlash('success', 'Activity created successfully!');
      return $this->redirectToRoute('app_activity_show', ['id' => $activity->getId()]);
  }

  // Si le formulaire n'est pas valide
  foreach ($form->getErrors(true) as $error) {
      $this->addFlash('error', $error->getMessage());
  }
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
    SluggerInterface $slugger,
    ValidatorInterface $validator
): Response {
    $activity = $entityManager->getRepository(Activité::class)->find($id);
    
    if (!$activity) {
        $this->addFlash('error', 'Activity not found');
        return $this->redirectToRoute('app_activity_index');
    }

    // CSRF token validation
    if (!$this->isCsrfTokenValid('update_activity_'.$activity->getId(), $request->request->get('_token'))) {
        $this->addFlash('error', 'Invalid CSRF token');
        return $this->redirectToRoute('app_activity_edit', ['id' => $id]);
    }

    // Handle image removal
    if ($request->request->get('remove_image') === '1') {
        $this->handleImageRemoval($activity);
    }

    // Update activity data
    $activity->setNom($request->request->get('nom'));
    $activity->setDescription($request->request->get('description'));
    $activity->setType(ActivityType::from($request->request->get('type')));

    // Validate the activity
    $errors = $validator->validate($activity);
    if (count($errors) > 0) {
        foreach ($errors as $error) {
            $this->addFlash('error', $error->getMessage());
        }
        return $this->redirectToRoute('app_activity_edit', ['id' => $id]);
    }

    // Handle new image upload
    $imageFile = $request->files->get('activityImage');
    if ($imageFile) {
        $this->handleImageUpload($activity, $imageFile, $slugger);
    }

    $entityManager->flush();

    $this->addFlash('success', 'Activity updated successfully!');
    return $this->redirectToRoute('app_activity_index');
}

private function handleImageRemoval(Activité $activity): void
{
    $imagePath = $activity->getUrl();
    if ($imagePath) {
        $fullPath = $this->getParameter('kernel.project_dir').'/public'.$imagePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        $activity->setUrl(null);
    }
}

private function handleImageUpload(Activité $activity, UploadedFile $imageFile, SluggerInterface $slugger): void
{
    // Remove old image if exists
    if ($activity->getUrl()) {
        $this->handleImageRemoval($activity);
    }

    // Generate unique filename
    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = $slugger->slug($originalFilename);
    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

    // Move the file to uploads directory
    $uploadsDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/activities/';
    $imageFile->move($uploadsDirectory, $newFilename);

    // Update activity with new image path
    $activity->setUrl('/uploads/activities/'.$newFilename);
}
}