<?php

namespace App\Service;

use Google\Client;
use Google\Service\Calendar;

class GoogleCalendarService
{
    private Client $client;
    
    

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(__DIR__ . '/../../config/google_client_secret.json');
        $this->client->addScope(Calendar::CALENDAR_EVENTS);
        $this->client->setRedirectUri('http://127.0.0.1:8000/google/callback');
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setIncludeGrantedScopes(true);

    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function getClient(): Client
    {
        return $this->client;
    }



}
