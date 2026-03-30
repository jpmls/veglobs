<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RoutePlannerController extends AbstractController
{
    #[Route('/route-planner', name: 'app_route_planner')]
    public function index(): Response
    {
        return $this->render('pages/route_planner.html.twig');
    }
}