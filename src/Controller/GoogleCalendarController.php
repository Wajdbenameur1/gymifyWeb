<?php

namespace App\Controller;

use App\Service\GoogleCalendarService;
use Google\Service\Calendar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;

class GoogleCalendarController extends AbstractController
{
  public function __construct(private EntityManagerInterface $entityManager)
{
}

    #[Route('/google/auth', name: 'google_auth')]
    public function googleAuth(GoogleCalendarService $googleCalendarService): Response
    {
        return $this->redirect($googleCalendarService->getAuthUrl());
    }

    #[Route('/google/callback', name: 'google_callback')]
    public function googleCallback(Request $request, GoogleCalendarService $googleCalendarService, SessionInterface $session): Response
    {
        $client = $googleCalendarService->getClient();
        $code = $request->query->get('code');

        if ($code) {
            $accessToken = $client->fetchAccessTokenWithAuthCode($code);
            $session->set('google_access_token', $accessToken);

            return $this->redirectToRoute('app_calendar');
        }

        return $this->redirectToRoute('app_entraineur');
    }

    

  }