<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JourneyController extends AbstractController
{
    #[Route('/planificateur', name: 'app_journey_planner')]
    public function index(): Response
    {

    return $this->render('pages/journey.html.twig');

    }
}