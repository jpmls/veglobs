<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TransportStopApiController extends AbstractController
{
    #[Route('/api/stops', name: 'api_stops', methods: ['GET'])]
    public function stops(): JsonResponse
    {
        $projectDir = $this->getParameter('kernel.project_dir');

        $stationsPath = $projectDir . '/data/stations.csv';
        $coordsPath = $projectDir . '/data/emplacement-des-gares-idf.csv';

        $stations = $this->loadStations($stationsPath);
        $coords = $this->loadCoordsByNameAndLine($coordsPath);

        $result = [];

        foreach ($stations as $station) {
            $key = $this->buildKey($station['name'], $station['line']);

            if (!isset($coords[$key])) {
                continue;
            }

            $result[] = [
                'id' => $station['id'],           // ✅ id de stations.csv
                'name' => $station['name'],
                'lat' => $coords[$key]['lat'],
                'lng' => $coords[$key]['lng'],
                'line' => $station['line'],
                'terminus' => $station['terminus'],
            ];
        }

        return $this->json($result);
    }

    private function loadStations(string $filePath): array
    {
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            return [];
        }

        $headers = fgetcsv($handle, 0, ';');
        $stations = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (!$headers || count($row) !== count($headers)) {
                continue;
            }

            $item = array_combine($headers, $row);

            $id = isset($item['id']) ? trim((string) $item['id']) : null;
            $line = isset($item['ligne']) ? trim((string) $item['ligne']) : null;
            $terminus = isset($item['terminus']) ? trim((string) $item['terminus']) : null;
            $name = isset($item['nom']) ? trim((string) $item['nom']) : null;

            if ($id === null || $name === null || $line === null) {
                continue;
            }

            $stations[] = [
                'id' => $id,
                'name' => str_replace('_', ' ', $name),
                'line' => $line,
                'terminus' => $terminus,
            ];
        }

        fclose($handle);

        return $stations;
    }

    private function loadCoordsByNameAndLine(string $filePath): array
    {
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            return [];
        }

        $headers = fgetcsv($handle, 0, ';');
        $coords = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (!$headers || count($row) !== count($headers)) {
                continue;
            }

            $item = array_combine($headers, $row);

            $name = $item['NOM_GARE'] ?? null;
            $line = $item['LIGNE'] ?? null;
            $geoPoint = $item['Geo Point'] ?? null;

            if ($name === null || $line === null || $geoPoint === null) {
                continue;
            }

            $parts = explode(',', $geoPoint);

            if (count($parts) !== 2) {
                continue;
            }

            $key = $this->buildKey($name, $line);

            $coords[$key] = [
                'lat' => (float) trim($parts[0]),
                'lng' => (float) trim($parts[1]),
            ];
        }

        fclose($handle);

        return $coords;
    }

    private function buildKey(string $name, string $line): string
    {
        return $this->normalizeName($name) . '|' . mb_strtolower(trim($line));
    }

    private function normalizeName(string $name): string
    {
        $name = str_replace('_', ' ', $name);
        $name = mb_strtolower(trim($name));
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $name = str_replace(['-', '\'', '’'], ' ', $name);
        $name = preg_replace('/[^a-z0-9 ]/', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }
}