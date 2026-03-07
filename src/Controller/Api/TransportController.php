<?php

namespace App\Controller\Api;

use App\Repository\Transport\TransportLineRepository;
use App\Repository\Transport\TransportStopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/transport')]
class TransportController extends AbstractController
{
    #[Route('/lines', name: 'api_transport_lines', methods: ['GET'])]
    public function lines(Request $request, TransportLineRepository $repository): JsonResponse
    {
        $items = $repository->findBy([], ['shortName' => 'ASC'], 200);

        $data = array_map(function ($line) {
            return [
                'id' => $line->getId(),
                'externalId' => $line->getExternalId(),
                'name' => $line->getName(),
                'shortName' => $line->getShortName(),
                'mode' => $line->getTransportMode(),
                'network' => $line->getNetworkName(),
                'color' => $line->getColorHex(),
            ];
        }, $items);

        return $this->json([
            'count' => count($data),
            'data' => $data
        ]);
    }

    #[Route('/stops/search', name: 'api_transport_stop_search', methods: ['GET'])]
    public function search(Request $request, TransportStopRepository $repository): JsonResponse
    {
        $q = trim((string) $request->query->get('q'));

        if (!$q) {
            return $this->json([
                'error' => 'q parameter required'
            ], 400);
        }

        $stops = $repository->searchByName($q, 20);

        $data = array_map(function ($stop) {
            return [
                'id' => $stop->getId(),
                'name' => $stop->getName(),
                'town' => $stop->getTown(),
                'lat' => $stop->getLat(),
                'lon' => $stop->getLon(),
            ];
        }, $stops);

        return $this->json([
            'count' => count($data),
            'data' => $data
        ]);
    }
}