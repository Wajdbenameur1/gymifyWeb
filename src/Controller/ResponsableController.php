<?php
namespace App\Controller;

use App\Entity\ResponsableSalle;
use App\Enum\Role;
use App\Repository\SalleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ResponsableController extends AbstractController
{
    #[Route('/responsable', name: 'dashboard_responsable_salle')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_' . strtoupper(Role::RESPONSABLE_SALLE->value));

        $stats = [
            'events' => 150,
            'equipes' => 25,
            'salle_nom' => $this->getUser()?->getSalle()?->getNom() ?? 'Ma Salle'
        ];

        return $this->render('responsable/index.html.twig', [
            'stats' => $stats,
            'page_title' => 'Tableau de bord Responsable'
        ]);
    }

    #[Route('/responsable/home', name: 'app_responsable_home')]
    public function home(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_' . strtoupper(Role::RESPONSABLE_SALLE->value));

        return $this->render('responsable/home.html.twig', [
            'page_title' => 'Accueil Responsable'
        ]);
    }

    #[Route('/responsable/dashboard', name: 'app_responsable_dashboard')]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_' . strtoupper(Role::RESPONSABLE_SALLE->value));

        $stats = [
            'events' => 150,
            'equipes' => 25,
            'salle_nom' => $this->getUser()?->getSalle()?->getNom() ?? 'Ma Salle'
        ];

        return $this->render('responsable/index.html.twig', [
            'stats' => $stats,
            'page_title' => 'Tableau de bord Responsable'
        ]);
    }

    #[Route('/responsable/profile', name: 'app_responsable_profile')]
    public function profile(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_' . strtoupper(Role::RESPONSABLE_SALLE->value));

        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir votre profil responsable.');
        }

        return $this->render('responsable/show.html.twig', [
            'user' => $user,
            'page_title' => 'Profil Responsable'
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'page_title' => 'Connexion'
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method should be intercepted by the logout key on your firewall.');
    }

    #[Route('/responsable/about', name: 'app_responsable_about')]
    public function about(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_' . strtoupper(Role::RESPONSABLE_SALLE->value));

        return $this->render('responsable/about.html.twig', [
            'page_title' => 'À propos Responsable'
        ]);
    }

    #[Route('/responsable/help', name: 'app_responsable_help')]
    public function help(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_' . strtoupper(Role::RESPONSABLE_SALLE->value));

        return $this->render('responsable/help.html.twig', [
            'page_title' => 'Aide Responsable'
        ]);
    }

    #[Route('/responsable/terms', name: 'app_responsable_terms')]
    public function terms(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_' . strtoupper(Role::RESPONSABLE_SALLE->value));

        return $this->render('responsable/terms.html.twig', [
            'page_title' => 'Conditions d\'utilisation Responsable'
        ]);
    }

    #[Route('/responsable/mygym', name: 'app_responsable_mygym')]
    public function myGym(SalleRepository $salleRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_' . strtoupper(Role::RESPONSABLE_SALLE->value));

        $user = $this->getUser();
        if (!$user instanceof ResponsableSalle) {
            throw $this->createAccessDeniedException();
        }

        $salle = $user->getSalle();
        if (!$salle) {
            $this->addFlash('warning', 'Aucune salle associée à votre compte');
            return $this->redirectToRoute('app_salle_create');
        }

        $freshSalle = $salleRepo->find($salle->getId());

        $stats = [
            'events' => $freshSalle->getEvents() instanceof \Countable ? $freshSalle->getEvents()->count() : 0,
            'equipes' => method_exists($freshSalle, 'getEquipes') ? $freshSalle->getEquipes()->count() : 0
        ];

        return $this->render('responsable/mygym.html.twig', [
            'salle' => $freshSalle,
            'page_title' => 'Ma Salle - ' . $freshSalle->getNom(),
            'stats' => $stats
        ]);
    }
}