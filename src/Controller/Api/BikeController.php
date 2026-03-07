<?php

namespace App\Controller\Api;

use App\Repository\Transport\BikeStationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/bikes')]
class BikeController extends AbstractController
{
    #[Route('/stations', name: 'api_bikes_stations', methods: ['GET'])]
    public function stations(BikeStationRepository $repository): JsonResponse
    {
        $stations = $repository->findAvailable(300);

        $data = array_map(function ($station) {
            return [
                'id' => $station->getId(),
                'name' => $station->getName(),
                'lat' => $station->getLat(),
                'lon' => $station->getLon(),
                'availableBikes' => $station->getAvailableBikes(),
                'availableStands' => $station->getAvailableBikeStands(),
            ];
        }, $stations);

        return $this->json([
            'count' => count($data),
            'data' => $data
        ]);
    }
}