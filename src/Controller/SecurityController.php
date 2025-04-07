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
        // Si l'utilisateur est déjà connecté, on le redirige vers le tableau de bord
        if ($this->getUser()) {
            return $this->redirectToRoute($this->getDashboardRouteByRole());
        }

        // Sinon, on affiche la page de connexion avec des messages d'erreur ou le dernier nom d'utilisateur
        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode est interceptée par Symfony lors de la déconnexion
        throw new \LogicException('Cette méthode peut rester vide — elle est interceptée par Symfony lors de la déconnexion.');
    }

    private function getDashboardRouteByRole(): string
    {
        $user = $this->getUser();

        if (!$user) {
            return 'app_home';
        }

        // On récupère le premier rôle de l'utilisateur (assumant que l'utilisateur a un seul rôle majeur)
        $role = $user->getRoles();

        // Comparaison du rôle et redirection vers le tableau de bord approprié
        // Vérification des rôles définis dans l'énumération Role
        if (in_array(Role::ADMIN->value, $role)) {
            return 'app_admin';
        }
        
        if (in_array(Role::SPORTIF->value, $role)) {
            return 'app_admin';
        }
        
        if (in_array(Role::ENTRAINEUR->value, $role)) {
            return 'dashboard_entraineur';
        }
        
        if (in_array(Role::RESPONSABLE_SALLE->value, $role)) {
            return 'dashboard_responsable_salle';
        }

        // Si aucun rôle spécifique n'est trouvé, on redirige vers la page d'accueil
        return 'app_home';
    }
}
