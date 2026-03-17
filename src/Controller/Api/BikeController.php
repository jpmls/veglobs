<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class BikeController extends AbstractController
{
    #[Route('/api/bikes/stations', name: 'api_bike_stations', methods: ['GET'])]
    public function stations(HttpClientInterface $httpClient): JsonResponse
    {
        $url = 'https://opendata.paris.fr/api/explore/v2.1/catalog/datasets/velib-disponibilite-en-temps-reel/exports/geojson?lang=fr&timezone=Europe%2FParis';

        $response = $httpClient->request('GET', $url);
        $geojson = $response->toArray();

        $features = $geojson['features'] ?? [];
        $stations = [];

        foreach ($features as $feature) {
            $geometry = $feature['geometry'] ?? [];
            $properties = $feature['properties'] ?? [];

            $coordinates = $geometry['coordinates'] ?? null;

            if (
                !is_array($coordinates) ||
                count($coordinates) < 2 ||
                !isset($coordinates[0], $coordinates[1])
            ) {
                continue;
            }

            $lon = is_numeric($coordinates[0]) ? (float) $coordinates[0] : null;
            $lat = is_numeric($coordinates[1]) ? (float) $coordinates[1] : null;

            $city = $properties['nom_arrondissement_communes'] ?? null;
            $codeInsee = $properties['code_insee_commune'] ?? null;
            $stationCode = isset($properties['stationcode']) ? (string) $properties['stationcode'] : null;

            $arrondissement = null;

            // Paris uniquement : on déduit l'arrondissement depuis le début du stationcode
            // Exemples : 9020 -> 9, 14014 -> 14, 17041 -> 17
            if (($city === 'Paris' || $codeInsee === '75056') && $stationCode !== null) {
                $digits = preg_replace('/\D+/', '', $stationCode);

                if ($digits !== '') {
                    $value = (int) $digits;
                    $guess = (int) floor($value / 1000);

                    if ($guess >= 1 && $guess <= 20) {
                        $arrondissement = $guess;
                    }
                }
            }

            $stations[] = [
                'id' => $stationCode ?? uniqid('station_', true),
                'stationCode' => $stationCode,
                'name' => $properties['name'] ?? 'Station',
                'lat' => $lat,
                'lon' => $lon,
                'city' => $city,
                'codeInsee' => $codeInsee,
                'arrondissement' => $arrondissement,
                'availableBikes' => $properties['numbikesavailable'] ?? 0,
                'availableStands' => $properties['numdocksavailable'] ?? 0,
                'mechanical' => $properties['mechanical'] ?? 0,
                'ebike' => $properties['ebike'] ?? 0,
                'capacity' => $properties['capacity'] ?? null,
                'isInstalled' => $properties['is_installed'] ?? null,
                'isRenting' => $properties['is_renting'] ?? null,
                'isReturning' => $properties['is_returning'] ?? null,
                'updatedAt' => $properties['duedate'] ?? null,
            ];
        }

        usort($stations, function (array $a, array $b): int {
            return strcmp((string) ($a['city'] ?? ''), (string) ($b['city'] ?? ''));
        });

        return $this->json([
            'count' => count($stations),
            'data' => $stations,
        ]);
    }
}