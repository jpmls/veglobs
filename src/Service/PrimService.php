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

    /**
     * Calcule un itinéraire entre deux points
     * @param string $from  "lat;lon" ou nom de lieu
     * @param string $to    "lat;lon" ou nom de lieu
     * @param string $datetime  format YYYYMMDDTHHmmss (optionnel)
     */
    public function getJourney(string $from, string $to, string $datetime = ''): array
    {
        $params = [
            'from'  => $from,
            'to'    => $to,
            'count' => 3,
        ];

        if ($datetime) {
            $params['datetime'] = $datetime;
        }

        $query = http_build_query($params);

        $response = $this->client->request(
            'GET',
            "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/journeys?{$query}",
            [
                'headers' => [
                    'apikey' => $_ENV['IDFM_API_KEY'] ?? '',
                    'Accept' => 'application/json',
                ],
            ]
        );

        return $response->toArray(false);
    }

    /**
     * Recherche un lieu / arrêt par nom (geocoding Navitia)
     */
    public function searchPlace(string $query): array
    {
        $params = http_build_query([
            'q'    => $query,
            'type[]' => ['stop_area', 'address'],
            'count' => 10,
        ]);

        $response = $this->client->request(
            'GET',
            "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/places?{$params}",
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