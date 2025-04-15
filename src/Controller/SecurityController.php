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

    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]

    

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute($this->getDashboardRouteByRole());
        }
    
        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
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
    
        $routes = [
            Role::ADMIN->value => 'app_admin',
            Role::ENTRAINEUR->value => 'app_entraineur',
            Role::RESPONSABLE_SALLE->value => 'dashboard_responsable_salle',
            Role::SPORTIF->value => 'home',
        ];
    
        return $routes[$user->getRole()] ?? 'app_home';
    }
    
}