<?php
namespace App\Controller;

use App\Entity\Sportif;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegistrationController extends AbstractController
{
    #[Route('/register/sportif', name: 'register_sportif')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $sportif = new Sportif();
        $form = $this->createForm(UserType::class, $sportif);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($sportif, $plainPassword);
            $sportif->setPassword($hashedPassword);

            // définir le rôle
            $sportif->setRole('sportif'); // ou selon votre enum Role::SPORTIF

            $em->persist($sportif);
            $em->flush();

            $this->addFlash('success', 'Inscription réussie !');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
