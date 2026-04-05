<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PrimeService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;

        $this->apiKey = 'K9Dts13skxsn209BKmHDm2SewskchljY';
    }

    public function getDisruptions(): array
    {
        $response = $this->client->request('GET',
            'https://api.iledefrance-mobilites.fr/marketplace/disruptions',
            [
                'headers' => [
                    'apikey' => $this->apiKey
                ]
            ]
        );

        return $response->toArray();
    }
}