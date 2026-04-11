<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(NewsRepository $repo): Response
    {
        $result = $repo->search([], 1, 3);

        return $this->render('home/home.html.twig', [
            'latestNews' => $result['items'],
        ]);
    }
}