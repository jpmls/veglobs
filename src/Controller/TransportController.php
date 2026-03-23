<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransportController extends AbstractController
{
    #[Route('/map', name: 'transport_map')]
    public function map(): Response
    {
        return $this->render('transport/map.html.twig');
    }
}