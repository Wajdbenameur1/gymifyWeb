<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ReCaptchaService
{
    private $client;
    private $secretKey;

    public function __construct(HttpClientInterface $client, string $secretKey)
    {
        $this->client = $client;
        $this->secretKey = $secretKey;
    }

    public function verify(string $response, string $ip): bool
    {
        $response = $this->client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
            'query' => [
                'secret' => $this->secretKey,
                'response' => $response,
                'remoteip' => $ip,
            ],
        ]);

        $data = $response->toArray();

        return $data['success'] ?? false;
    }
}
