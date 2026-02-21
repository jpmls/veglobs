<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Annotation\IsGranted;

class NewsController extends AbstractController

{
    // GET /api/news
    #[Route('/api/news', name: 'api_news_list', methods: ['GET'])]
    public function list(Request $request, NewsRepository $newsRepository, SerializerInterface $serializer): JsonResponse
    {
        // Code pour GET /api/news avec pagination et filtres
    }

    // GET /api/news/{id}
    #[Route('/api/news/{id}', name: 'api_news_detail', methods: ['GET'])]
    public function show(int $id, NewsRepository $newsRepository, SerializerInterface $serializer): JsonResponse
    {
        // Code pour GET /api/news/{id}
    }

    // POST /api/news
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/news', name: 'api_news_create', methods: ['POST'])]
    public function create(Request $request, NewsRepository $newsRepository): JsonResponse
    {
        // Code pour POST /api/news
    }

    // PUT /api/news/{id}
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/news/{id}', name: 'api_news_edit', methods: ['PUT'])]
    public function edit(int $id, Request $request, NewsRepository $newsRepository): JsonResponse
    {
        // Code pour PUT /api/news/{id}
    }

    // DELETE /api/news/{id}
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/news/{id}', name: 'api_news_delete', methods: ['DELETE'])]
    public function delete(int $id, NewsRepository $newsRepository): JsonResponse
    {
        // Code pour DELETE /api/news/{id}
    }
}