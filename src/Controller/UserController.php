<?php
namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
class UserController extends AbstractController
{
    private $userRepository;
    private $entityManager;
    private $passwordHasher;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    // Afficher la liste des utilisateurs
    #[Route('/admin/user', name: 'user_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $users = $this->userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    // Créer un nouvel utilisateur
    #[Route('/admin/user/create', name: 'user_create')]
#[IsGranted('ROLE_ADMIN')]
public function create(Request $request): Response
{
    $user = new User();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('user_index');
    }

    return $this->render('user/user_create.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/admin/user/{id}/edit', name: 'user_edit')]
public function edit(Request $request, User $user, SluggerInterface $slugger): Response
{
    // Créer le formulaire pour l'utilisateur
    $form = $this->createForm(UserType::class, $user, [
        'is_entraineur' => $user->getRole() === Role::ENTRAINEUR ? true : false,
    ]);
    $form->handleRequest($request);

    // Si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion de l'image
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('imageUrl')->getData();

        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            // Déplace le fichier dans le répertoire où les images sont stockées
            try {
                $imageFile->move(
                    $this->getParameter('images_directory'), // Chemin dans le service.yaml
                    $newFilename
                );
            } catch (FileException $e) {
                // Gérer l'exception si le fichier ne peut pas être déplacé
            }

            // Mettre à jour l'URL de l'image dans l'entité
            $user->setImageUrl($newFilename);
        }

        // Enregistrer les modifications dans la base de données
        $this->entityManager->flush();

        // Redirection après la modification
        return $this->redirectToRoute('user_index');
    }

    // Retourner le formulaire à la vue
    return $this->render('user/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}


    // Supprimer un utilisateur
    #[Route('/admin/user/{id}/delete', name: 'user_delete')]
    public function delete(User $user): Response
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('user_index');
    }

    // Afficher les détails d'un utilisateur
    #[Route('/admin/user/{id}', name: 'user_show')]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
}
