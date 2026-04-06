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
                'id'                => $line->getId(),
                'name'              => $line->getName(),
                'transport_mode'    => $line->getTransportMode(),
                'transport_submode' => $line->getTransportSubmode(),
            ];
        }, $lines);
        return $this->json($data);
    }

    #[Route('/api/stops/by-type', name: 'api_stops_by_type', methods: ['GET'])]
    public function stopsByType(Request $request, Connection $connection): JsonResponse
    {
        $type = $request->query->get('type', 'all');

        // Récupérer les stops avec la ligne associée via short_name + transport_mode
        // On utilise transport_line pour enrichir les stops avec le numéro et la couleur
        $baseWhere = "(
            (lat IS NOT NULL AND lon IS NOT NULL)
            OR
            (x_epsg2154 IS NOT NULL AND y_epsg2154 IS NOT NULL)
        )";

        if ($type === 'all') {
            $sql = "
                SELECT id, name, x_epsg2154, y_epsg2154, lat, lon, stop_type, town
                FROM transport_stop
                WHERE {$baseWhere}
                ORDER BY id DESC
            ";
            $stops = $connection->fetchAllAssociative($sql);
        } else {
            $sql = "
                SELECT id, name, x_epsg2154, y_epsg2154, lat, lon, stop_type, town
                FROM transport_stop
                WHERE stop_type = :type
                AND {$baseWhere}
                ORDER BY id DESC
            ";
            $stops = $connection->fetchAllAssociative($sql, ['type' => $type]);
        }

        // Enrichir avec les lignes du mode (une couleur par mode)
        $linesSql = "SELECT short_name, color_hex FROM transport_line WHERE transport_mode = :mode AND short_name IS NOT NULL ORDER BY short_name ASC LIMIT 20";
        $modeForLines = $type === 'all' ? 'metro' : $type;
        $lines = $connection->fetchAllAssociative($linesSql, ['mode' => $modeForLines]);

        return $this->json([
            'type'  => $type,
            'stops' => $stops,
            'lines' => $lines,
        ]);
    }

    #[Route('/api/stops/search', name: 'api_stops_search', methods: ['GET'])]
    public function searchStops(Request $request, Connection $connection): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));
        $type  = trim((string) $request->query->get('type', 'all'));

        if ($query === '') {
            return $this->json([]);
        }

        $sql = "
            SELECT
                s.id, s.name, s.x_epsg2154, s.y_epsg2154,
                s.lat, s.lon, s.stop_type, s.town,
                l.short_name AS line,
                l.color_hex AS line_color
            FROM transport_stop s
            LEFT JOIN transport_line l ON l.transport_mode = s.stop_type
            WHERE s.name LIKE :query
            AND (
                (lat IS NOT NULL AND lon IS NOT NULL)
                OR
                (x_epsg2154 IS NOT NULL AND y_epsg2154 IS NOT NULL)
            )
        ";

        $params = ['query' => '%' . $query . '%'];

        if ($type !== 'all') {
            $sql .= " AND s.stop_type = :type";
            $params['type'] = $type;
        }

        $sql .= " GROUP BY s.id ORDER BY s.name ASC LIMIT 10";

        $stops = $connection->fetchAllAssociative($sql, $params);

        return $this->json($stops);
    }

    /**
     * Retourne toutes les lignes d'un mode avec leurs couleurs
     * GET /api/lines/by-mode?mode=metro
     */
    #[Route('/api/lines/by-mode', name: 'api_lines_by_mode', methods: ['GET'])]
    public function linesByMode(Request $request, Connection $connection): JsonResponse
    {
        $mode = $request->query->get('mode', 'metro');

        $sql = "
            SELECT short_name, color_hex, text_color_hex, transport_mode
            FROM transport_line
            WHERE transport_mode = :mode
            AND short_name IS NOT NULL
            ORDER BY short_name ASC
        ";

        $lines = $connection->fetchAllAssociative($sql, ['mode' => $mode]);

        return $this->json($lines);
    }
}