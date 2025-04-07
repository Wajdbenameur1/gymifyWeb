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

        $roles = $user->getRoles();

        if (in_array(Role::ADMIN->value, $roles)) {
            return 'dashboard_admin';
        }

        if (in_array(Role::SPORTIF->value, $roles)) {
            return 'home';
        }

        if (in_array(Role::ENTRAINEUR->value, $roles)) {
            return 'dashboard_entraineur';
        }

        if (in_array(Role::RESPONSABLE_SALLE->value, $roles)) {
            return 'dashboard_responsable_salle';
        }

        return 'app_home';
    }
}
