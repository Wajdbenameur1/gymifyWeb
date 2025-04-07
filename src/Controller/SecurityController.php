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
    }
    private function getDashboardRouteByRole(): string
    {
        $user = $this->getUser();
    
        if (!$user) {
            return 'app_home';
        }
    
        $role = $user->getRole(); // ex: "admin", "entraineur", etc.
    
        if ($role === Role::ADMIN->value) {
            return 'dashboard_admin';
        }
    
        if ($role === Role::ENTRAINEUR->value) {
            return 'dashboard_entraineur';
        }
    
        if ($role === Role::RESPONSABLE_SALLE->value) {
            return 'dashboard_responsable_salle';
        }
    
        if ($role === Role::SPORTIF->value) {
            return 'home';
        }
    
        return 'app_home';
    }
}
