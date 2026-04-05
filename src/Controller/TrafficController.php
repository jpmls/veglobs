<?php

namespace App\Controller;

use App\Service\TrafficService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TrafficController extends AbstractController
{
    #[Route('/traffic', name: 'app_traffic')]
    public function index(TrafficService $service): Response
    {
        $items = $service->getTraffic();

        return $this->render('traffic/index.html.twig', [
            'items' => $items,
        ]);
    }
}