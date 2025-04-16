<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Form\UserType;
use App\Service\EmailService;
use App\Repository\UserRepository;
use App\Service\EmailUniquenessValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class UserController extends AbstractController
{
    private EmailUniquenessValidator $emailUniquenessValidator;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private SluggerInterface $slugger;

    public function __construct(
        EmailUniquenessValidator $emailUniquenessValidator,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger
    ) {
        $this->emailUniquenessValidator = $emailUniquenessValidator;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->slugger = $slugger;
    }

    #[Route('/admin/user', name: 'user_index')]
    public function index(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('/user/index.html.twig', [
            'users' => $users,
            'page_title' => 'Gestion des utilisateurs',
        ]);
    }

    #[Route('/admin/user/create', name: 'user_create')]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, EmailService $emailService): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('create_user', $submittedToken)) {
                $this->addFlash('error', 'Token CSRF invalide');
                return $this->redirectToRoute('user_create');
            }

            if (empty($user->getPassword())) {
                $this->addFlash('error', 'Le mot de passe ne peut pas être vide.');
                return $this->render('user/user_create.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            if ($user->getRole() === Role::ENTRAINEUR && empty($user->getSpecialite())) {
                $this->addFlash('danger', 'La spécialité est requise pour les entraîneurs.');
                return $this->render('user/user_create.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $imageFile = $form->get('imageUrl')->getData();
            if ($imageFile) {
                try {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $this->slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/users';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $imageFile->move($uploadDir, $newFilename);
                    $user->setImageUrl('/uploads/users/'.$newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image: ' . $e->getMessage());
                    return $this->render('user/user_create.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $emailService->sendRegistrationEmail($user);

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/user_create.html.twig', [
            'form' => $form->createView(),
            'page_title' => 'Ajouter un utilisateur',
        ]);
    }

    #[Route('/admin/user/{id}/edit', name: 'app_user_update')]
    public function update(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $this->addFlash('error', 'L\'email est déjà utilisé.');
                return $this->render('user/edit.html.twig', ['form' => $form->createView()]);
            }

            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $imageFile = $form->get('imageUrl')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/users';
                $imageFile->move($uploadDir, $newFilename);
                $user->setImageUrl('/uploads/users/'.$newFilename);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'L\'utilisateur a été mis à jour avec succès.');
            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/user/{id}/delete', name: 'user_delete')]
    public function delete(User $user): Response
    {
        if ($user->getImageUrl()) {
            $imagePath = $this->getParameter('kernel.project_dir').'/public'.$user->getImageUrl();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('user_index');
    }

    #[Route('/admin/user/{id}', name: 'user_show', requirements: ['id' => '\d+'])]
    public function showById(int $id): Response
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/user/email/{email}', name: 'admin_user_show')]
    public function showByEmail(string $email): Response
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
}
