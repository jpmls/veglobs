<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiTestController extends AbstractController
{
    #[Route('/test-api', name: 'test_api', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('test_api/index.html.twig');
    }
}