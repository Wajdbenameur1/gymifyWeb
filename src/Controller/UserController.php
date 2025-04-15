<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Sportif;
use App\Entity\ResponsableSalle;
use App\Entity\Entraineur;
use App\Enum\Role;
use App\Entity\User;
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
use Symfony\Component\HttpFoundation\JsonResponse;

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

    // Afficher la liste des utilisateurs
    #[Route('/admin/users', name: 'user_index')]
    public function index(): Response
    {
        $users = $this->userRepository->findAll();

        $this->logger->info('Utilisateurs récupérés', ['count' => count($users)]);

        if (empty($users)) {
            $this->addFlash('info', 'Aucun utilisateur trouvé dans la base de données.');
        }

        return $this->render('user/index.html.twig', [
            'page_title' => 'Liste des utilisateurs',
            'users' => $users,
        ]);
    }

    // Créer un nouvel utilisateur
    #[Route('/admin/user/create', name: 'user_create', methods: ['GET', 'POST'])]
    public function addUser(Request $request): Response
    {
        $user = new Sportif(); // valeur par défaut (car le formulaire a `data` => Role::SPORTIF)
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedRole = $form->get('role')->getData();

            // Crée dynamiquement la bonne instance selon le rôle choisi
            $userClass = match ($selectedRole) {
                Role::SPORTIF => Sportif::class,
                Role::ENTRAINEUR => Entraineur::class,
                Role::ADMIN => Admin::class,
                Role::RESPONSABLE_SALLE => ResponsableSalle::class,
                default => User::class,
            };

            $user = new $userClass();

            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            $existingUser = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $this->addFlash('danger', 'Cet email est déjà utilisé.');
                return $this->render('user/create.html.twig', [
                    'form' => $form->createView(),
                    'page_title' => 'Ajouter un utilisateur',
                ]);
            }

            // Hash du mot de passe
            $plainPassword = $form->get('password')->getData();
            if (!$plainPassword) {
                $this->addFlash('danger', 'Le mot de passe est requis.');
                return $this->render('user/create.html.twig', [
                    'form' => $form->createView(),
                    'page_title' => 'Ajouter un utilisateur',
                ]);
            }
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Image upload
            $imageFile = $form->get('imageUrl')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('upload_directory'),
                    $newFilename
                );
                $user->setImageUrl($newFilename);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur ajouté avec succès !');

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Ajouter un utilisateur',
        ]);
    }

    // Modifier un utilisateur existant
    #[Route('/admin/user/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function update(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user, [
            'validation_groups' => ['Default']
        ]);
        $form->remove('email')->remove('password');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
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
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $imageFile->move($uploadDir, $newFilename);
                    $user->setImageUrl($newFilename);
                }

                $this->entityManager->flush();
                $this->addFlash('success', 'Utilisateur mis à jour avec succès !');
                return $this->redirectToRoute('user_index');
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la mise à jour de l\'utilisateur', [
                    'message' => $e->getMessage(),
                    'email' => $user->getEmail() ?? 'N/A',
                ]);
                $this->addFlash('danger', 'Une erreur est survenue lors de la mise à jour de l\'utilisateur.');
            }
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Modifier un utilisateur',
            'user' => $user,
        ]);
    }

    // Supprimer un utilisateur
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
                $this->logger->error('Erreur lors de la suppression', [
                    'message' => $e->getMessage(),
                    'email' => $user->getEmail() ?? 'N/A',
                ]);
                $this->addFlash('danger', 'Erreur lors de la suppression : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('user_index');
    }

    // Afficher les détails d'un utilisateur
    #[Route('/admin/user/{id}', name: 'user_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showById(int $id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            $this->logger->warning('showById: User not found', ['id' => $id]);
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        $this->logger->info('showById: Rendering user details', ['user_id' => $id]);

        return $this->render('user/show.html.twig', [
            'page_title' => 'Détails de l\'utilisateur',
            'user' => $user,
        ]);
    }

    // Vérifier si un email existe déjà
    #[Route('/api/check-email', name: 'check_email', methods: ['GET'])]
    public function checkEmail(Request $request): JsonResponse
    {
        $email = $request->query->get('email');
        $user = $this->userRepository->findOneBy(['email' => $email]);

        return new JsonResponse(['exists' => $user !== null]);
    }
}
