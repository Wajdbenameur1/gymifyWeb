<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Enum\Role;

class SecurityController extends AbstractController
{
    
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
    
        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }
    #[Route(path: '/register/sportif', name: 'app_register_sportif')]
    public function registerSportif(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
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
    
        return $this->render('security/register_sportif.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method is intercepted by Symfony's firewall
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private function getDashboardRouteByRole(): string
    {
        $user = $this->getUser();
    
        if (!$user) {
            return 'app_home';
        }
    
        $role = $user->getRole();
    
        return match ($role) {
            Role::ADMIN->value => 'app_admin',
            Role::ENTRAINEUR->value => 'dashboard_entraineur',
            Role::RESPONSABLE_SALLE->value => 'dashboard_responsable_salle',
            Role::SPORTIF->value => 'home',
            default => 'app_home',
        };
    }
}