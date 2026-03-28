<?php

namespace App\Controller\Api;

use App\Repository\Transport\TransportLineRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class TransportLineController extends AbstractController
{
    #[Route('/api/lines', name: 'api_lines_index', methods: ['GET'])]
    public function index(TransportLineRepository $lineRepository): JsonResponse
    {
        $lines = $lineRepository->findAll();

        $data = array_map(function ($line) {
            return [
                'id' => $line->getId(),
                'name' => $line->getName(),
                'transport_mode' => $line->getTransportMode(),
                'transport_submode' => $line->getTransportSubmode(),
            ];
        }, $lines);

        return $this->json($data);
    }

    #[Route('/api/stops/by-type', name: 'api_stops_by_type', methods: ['GET'])]
    public function stopsByType(Request $request, Connection $connection): JsonResponse
    {
        $type = $request->query->get('type', 'all');

        if ($type === 'all') {
            $sql = "
                SELECT 
                    id,
                    name,
                    x_epsg2154,
                    y_epsg2154,
                    lat,
                    lon,
                    stop_type,
                    town
                FROM transport_stop
                WHERE
                    (lat IS NOT NULL AND lon IS NOT NULL)
                    OR
                    (x_epsg2154 IS NOT NULL AND y_epsg2154 IS NOT NULL)
                ORDER BY id DESC
            ";

            $stops = $connection->fetchAllAssociative($sql);
        } else {
            $sql = "
                SELECT 
                    id,
                    name,
                    x_epsg2154,
                    y_epsg2154,
                    lat,
                    lon,
                    stop_type,
                    town
                FROM transport_stop
                WHERE
                    stop_type = :type
                    AND (
                        (lat IS NOT NULL AND lon IS NOT NULL)
                        OR
                        (x_epsg2154 IS NOT NULL AND y_epsg2154 IS NOT NULL)
                    )
                ORDER BY id DESC
            ";

            $stops = $connection->fetchAllAssociative($sql, [
                'type' => $type
            ]);
        }

        return $this->json([
            'type' => $type,
            'stops' => $stops
        ]);
    }

    #[Route('/api/stops/search', name: 'api_stops_search', methods: ['GET'])]
    public function searchStops(Request $request, Connection $connection): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));
        $type = trim((string) $request->query->get('type', 'all'));

        if ($query === '') {
            return $this->json([]);
        }

        $sql = "
            SELECT
                id,
                name,
                x_epsg2154,
                y_epsg2154,
                lat,
                lon,
                stop_type,
                town
            FROM transport_stop
            WHERE
                name LIKE :query
                AND (
                    (lat IS NOT NULL AND lon IS NOT NULL)
                    OR
                    (x_epsg2154 IS NOT NULL AND y_epsg2154 IS NOT NULL)
                )
        ";

        $params = [
            'query' => '%' . $query . '%',
        ];

        if ($type !== 'all') {
            $sql .= " AND stop_type = :type";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY name ASC LIMIT 8";

        $stops = $connection->fetchAllAssociative($sql, $params);

        return $this->json($stops);
    }
}