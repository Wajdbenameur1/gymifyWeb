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
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
{
    $user = new User();
    $form = $this->createForm(UserType::class, $user);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $role = $user->getRole();
    
        // Si le rôle est "Entraîneur", on garde la spécialité
        if ($role === Role::ENTRAINEUR) {
            $specialite = $form->get('specialite')->getData();
            $user->setSpecialite($specialite);
        } else {
            $user->setSpecialite(null); // Sinon, on vide la spécialité
        }

        // Hash du mot de passe
        $user->setPassword(
            $passwordHasher->hashPassword($user, $user->getPassword())
        );

        // Gestion de l'image
        $imageFile = $form->get('imageUrl')->getData();
        if ($imageFile) {
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            try {
                $imageFile->move($this->getParameter('upload_directory'), $newFilename);
                $user->setImageUrl($newFilename);
            } catch (FileException $e) {
                // Gérer l'exception
            }
        }

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('user_index');
    }

    return $this->render('user/user_create.html.twig', [
        'form' => $form->createView(),
    ]);
}

 #[Route('/admin/user/{id}/edit', name: 'app_user_update')]
    public function update(Request $request, User $user, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification unicité de l'email
            $email = $user->getEmail();
            $existingUser = $this->userRepository->findOneBy(['email' => $email]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $this->addFlash('error', 'L\'email est déjà utilisé.');
                return $this->render('user/edit.html.twig', ['form' => $form->createView()]);
            }

            // Hachage du mot de passe si modifié
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Gestion de l'image de profil
            $imageFile = $form->get('imageUrl')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
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
    public function showById(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
    
        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }
    
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
    
    #[Route('/admin/user/email/{email}', name: 'admin_user_show')]
    public function showByEmail(string $email, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['email' => $email]);
    
        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }
    
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
    
    
}