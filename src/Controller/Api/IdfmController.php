<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IdfmController extends AbstractController
{
    #[Route('/api/idfm/stops', name: 'idfm_stops')]
    public function stops(HttpClientInterface $httpClient): JsonResponse
    {
        $response = $httpClient->request(
            'GET',
            'https://api.navitia.io/v1/coverage/fr-idf/stop_areas?count=20',
            [
                'auth_basic' => [$_ENV['IDFM_API_KEY'], ''],
            ]
        );

        return $this->json($response->toArray(false));
    }
}