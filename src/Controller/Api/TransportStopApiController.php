<?php

namespace App\Controller\Api;

use App\Repository\TransportStopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TransportStopApiController extends AbstractController
{
    #[Route('/api/stops', name: 'api_stops_list', methods: ['GET'])]
    public function list(TransportStopRepository $transportStopRepository): JsonResponse
    {
        $stops = $transportStopRepository->findAll();

        $data = [];

        foreach ($stops as $stop) {
            $data[] = [
                'id' => $stop->getId(),
                'name' => $stop->getName(),
                'x_epsg2154' => $stop->getXEPSG2154(),
                'y_epsg2154' => $stop->getYEPSG2154(),
            ];
        }

        return $this->json($data);
    }
}