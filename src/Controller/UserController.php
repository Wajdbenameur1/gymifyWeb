<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Form\UserType;
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
    private $emailUniquenessValidator;
    private $userRepository;
    private $entityManager;
    private $passwordHasher;

    public function __construct(
        EmailUniquenessValidator $emailUniquenessValidator,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->emailUniquenessValidator = $emailUniquenessValidator;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/admin/user', name: 'user_index')]
    public function index(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('user/index.html.twig', [
            'page_title' => 'Gestion des utilisateurs',
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/create', name: 'user_create')]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, SluggerInterface $slugger): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification CSRF
            $submittedToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('create_user', $submittedToken)) {
                $this->addFlash('error', 'Token CSRF invalide');
                return $this->redirectToRoute('user_create');
            }

            // Encoder le mot de passe
            if (!empty($user->getPassword())) {
                $encodedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($encodedPassword);
            } else {
                $this->addFlash('error', 'Le mot de passe ne peut pas être vide.');
                return $this->render('user/user_create.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Gestion de l'image
            $imageFile = $request->files->get('userImage');
            if ($imageFile) {
                try {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/users';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $imageFile->move($uploadDir, $newFilename);
                    $user->setProfilePicture('/uploads/users/'.$newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image: ' . $e->getMessage());
                    return $this->render('user/user_create.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }

            // Enregistrement
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/user_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/user/{id}/edit', name: 'app_user_update')]
    public function update(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $user->getEmail();
            if ($this->emailUniquenessValidator->emailExists($email)) {
                $this->addFlash('error', 'L\'email est déjà utilisé.');
                return $this->render('user/edit.html.twig', ['form' => $form->createView()]);
            }

            $rawPassword = $user->getPassword();
            if ($rawPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $rawPassword);
                $user->setPassword($hashedPassword);
            }

            $profilePicture = $form->get('imageUrl')->getData();
            if ($profilePicture) {
                $newFilename = uniqid() . '.' . $profilePicture->guessExtension();
                $profilePicture->move(
                    $this->getParameter('users_images_directory'),
                    $newFilename
                );
                $user->setImageUrl($newFilename);
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
            $imagePath = $this->getParameter('users_images_directory') . '/' . $user->getImageUrl();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');

        return $this->redirectToRoute('user_index');
    }

    #[Route('/admin/user/{id}', name: 'user_show')]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
}
