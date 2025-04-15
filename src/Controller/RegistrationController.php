<?php

namespace App\Controller;

use App\Entity\Sportif;
use App\Enum\Role;
use App\Form\SportifType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register/sportif', name: 'app_register_sportif')]
    public function registerSportif(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $sportif = new Sportif();
        $sportif->setRole(Role::SPORTIF); // Fixe automatiquement le rôle Sportif

        $form = $this->createForm(SportifType::class, $sportif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($sportif, $plainPassword);
            $sportif->setPassword($hashedPassword);

            $entityManager->persist($sportif);
            $entityManager->flush();

            $this->addFlash('success', 'Compte Sportif créé avec succès !');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
