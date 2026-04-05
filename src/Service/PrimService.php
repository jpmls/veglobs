<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PrimService
{
    public function __construct(
        private HttpClientInterface $client
    ) {}

    public function getDisruptions(): array
    {
        $response = $this->client->request(
            'GET',
            'https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/line_reports/line_reports?count=100',
            [
                'headers' => [
                    'apikey' => $_ENV['IDFM_API_KEY'] ?? '',
                    'Accept' => 'application/json',
                ],
            ]
        );

        return $response->toArray(false);
    }
}