<?php

namespace App\Controller;

use App\Entity\Activité; // Changé de 'activité' à 'Activite'
use App\Enum\ActivityType;
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
                $activity->setImageUrl('/uploads/activities/'.$newFilename);
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
}