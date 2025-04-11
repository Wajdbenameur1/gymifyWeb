<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Form\UserType;
use App\Service\EmailService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;

class UserController extends AbstractController
{
    private $userRepository;
    private $entityManager;
    private $passwordHasher;
    private $slugger;
    private $emailService;
    private $logger;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger,
        EmailService $emailService,
        LoggerInterface $logger
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->slugger = $slugger;
        $this->emailService = $emailService;
        $this->logger = $logger;
    }
    #[Route('/admin/user', name: 'user_index')]
    public function index(): Response
    {
        // Test avec findAll()
        $usersFindAll = $this->userRepository->findAll();
        $this->logger->info('Utilisateurs via findAll()', [
            'count' => count($usersFindAll),
            'users' => array_map(fn($u) => $u->getEmail(), $usersFindAll)
        ]);

        // Test avec la méthode de débogage
        $users = $this->userRepository->findAllUsersDebug();
        $this->logger->info('Utilisateurs via findAllUsersDebug()', [
            'count' => count($users),
            'users' => array_map(fn($u) => $u->getEmail(), $users)
        ]);

        if (empty($users)) {
            $this->addFlash('info', 'Aucun utilisateur trouvé dans la base de données.');
        }

        return $this->render('user/index.html.twig', [
            'page_title' => 'Liste des utilisateurs',
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/create', name: 'user_create', methods: ['GET', 'POST'])]
    public function addUser(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $existingUser = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
                if ($existingUser) {
                    $this->addFlash('danger', 'Cet email est déjà utilisé.');
                    return $this->render('user/user_create.html.twig', [
                        'form' => $form->createView(),
                        'page_title' => 'Ajouter un utilisateur',
                    ]);
                }

                $imageFile = $form->get('imageUrl')->getData();
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $this->slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/users';
                    $imageFile->move($uploadDir, $newFilename);
                    $user->setImageUrl($newFilename);
                }

                if ($user->getRole() === Role::ENTRAINEUR && !$user->getSpecialite()) {
                    $this->addFlash('danger', 'La spécialité est requise pour les entraîneurs.');
                    return $this->render('user/user_create.html.twig', [
                        'form' => $form->createView(),
                        'page_title' => 'Ajouter un utilisateur',
                    ]);
                }

                $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($hashedPassword);

                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $this->logger->info('Utilisateur ajouté', [
                    'id' => $user->getId(),
                    'email' => $user->getEmail()
                ]);

                $this->emailService->sendRegistrationEmail($user);

                $this->addFlash('success', 'Utilisateur ajouté avec succès !');
                return $this->redirectToRoute('user_index');
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de l\'ajout', [
                    'message' => $e->getMessage(),
                    'email' => $user->getEmail()
                ]);
                $this->addFlash('danger', 'Erreur lors de l\'ajout : ' . $e->getMessage());
            }
        }

        return $this->render('user/user_create.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Ajouter un utilisateur',
        ]);
    }

    // Les autres méthodes restent inchangées pour l'instant
    #[Route('/admin/user/{id}/edit', name: 'app_user_update', methods: ['GET', 'POST'])]
    public function update(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $existingUser = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
                if ($existingUser && $existingUser->getId() !== $user->getId()) {
                    $this->addFlash('danger', 'Cet email est déjà utilisé.');
                    return $this->render('user/edit.html.twig', [
                        'form' => $form->createView(),
                        'page_title' => 'Modifier un utilisateur',
                        'user' => $user,
                    ]);
                }

                $plainPassword = $form->get('password')->getData();
                if ($plainPassword) {
                    $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }

                $imageFile = $form->get('imageUrl')->getData();
                if ($imageFile) {
                    if ($user->getImageUrl()) {
                        $oldImagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/users/' . $user->getImageUrl();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $this->slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/users';
                    $imageFile->move($uploadDir, $newFilename);
                    $user->setImageUrl($newFilename);
                }

                $this->entityManager->flush();
                $this->addFlash('success', 'Utilisateur mis à jour avec succès !');
                return $this->redirectToRoute('user_index');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur lors de la mise à jour : ' . $e->getMessage());
            }
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Modifier un utilisateur',
            'user' => $user,
        ]);
    }

    #[Route('/admin/user/{id}/delete', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            try {
                if ($user->getImageUrl()) {
                    $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/users/' . $user->getImageUrl();
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }

                $this->entityManager->remove($user);
                $this->entityManager->flush();
                $this->addFlash('success', 'Utilisateur supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('user_index');
    }

    #[Route('/admin/user/{id}', name: 'user_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showById(int $id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'page_title' => 'Détails de l\'utilisateur',
        ]);
    }

    #[Route('/admin/user/email/{email}', name: 'admin_user_show', methods: ['GET'])]
    public function showByEmail(string $email): Response
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'page_title' => 'Détails de l\'utilisateur',
        ]);
    }
}