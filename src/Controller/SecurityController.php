<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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
        throw new \LogicException('Cette méthode peut rester vide — elle est interceptée par Symfony lors de la déconnexion.');
    }

    private function getDashboardRouteByRole(): string
    {
        $user = $this->getUser();

        if (!$user) {
            return 'app_home';
        }

        $role = $user->getRole(); // "admin", "sportif", etc.

        return match ($role) {
            'admin' => 'app_admin',
            'sportif' => 'home',
            'entraineur' => 'app_entraineurr',
            'responsable_salle' => 'dashboard_responsable_salle',
            default => 'app_home',
        };
    }
}
