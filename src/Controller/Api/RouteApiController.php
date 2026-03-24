<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RouteApiController extends AbstractController
{
    #[Route('/api/route', name: 'api_route', methods: ['GET'])]
    public function route(): JsonResponse
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $relationsPath = $projectDir . '/data/relations.csv';

        if (!file_exists($relationsPath)) {
            return $this->json([
                'error' => 'relations.csv introuvable',
                'path' => $relationsPath,
            ], 404);
        }

        $handle = fopen($relationsPath, 'r');

        if (!$handle) {
            return $this->json([
                'error' => 'Impossible d’ouvrir relations.csv',
            ], 500);
        }

        $firstLine = fgets($handle);
        rewind($handle);

        $headers = fgetcsv($handle, 0, ',');

        $rows = [];
        $count = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false && $count < 10) {
            $rows[] = $row;
            $count++;
        }

        fclose($handle);

        return $this->json([
            'path' => $relationsPath,
            'first_line' => $firstLine,
            'headers' => $headers,
            'sample_rows' => $rows,
        ]);
    }
}