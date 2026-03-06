<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NewsController extends AbstractController
{
    #[Route('/api/news', name: 'api_news_list', methods: ['GET'])]
    public function list(Request $request, NewsRepository $newsRepository): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', 20);

        if ($limit < 1 || $limit > 100) {
            return $this->json([
                'message' => 'Validation failed',
                'errors' => [
                    [
                        'field' => 'limit',
                        'message' => 'Must be between 1 and 100',
                    ],
                ],
            ], 422);
        }

        $filters = [
            'network' => $request->query->get('network'),
            'line' => $request->query->get('line'),
            'type' => $request->query->get('type'),
        ];

        $result = $newsRepository->search($filters, $page, $limit);

        $total = $result['total'];
        $pages = (int) max(1, ceil($total / $limit));

        return $this->json([
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => $pages,
            ],
            'data' => $result['items'],
        ], 200, [], ['groups' => ['news:read', 'user:read']]);
    }

    #[Route('/api/news/{id}', name: 'api_news_detail', methods: ['GET'])]
    public function show(int $id, NewsRepository $newsRepository): JsonResponse
    {
        $news = $newsRepository->find($id);

        if (!$news) {
            return $this->json([
                'message' => 'News not found',
            ], 404);
        }

        return $this->json($news, 200, [], ['groups' => ['news:read', 'user:read']]);
    }

    #[Route('/api/news', name: 'api_news_create', methods: ['POST'])]
    public function create(
        Request $request,
        NewsRepository $newsRepository,
        ValidatorInterface $validator
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_REDACTOR');

        $data = $this->decodeJsonObject($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $news = new News();
        $this->hydrateNews($news, $data);
        $news->setPublishedAt(new \DateTimeImmutable());

        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'message' => 'You must be logged in',
            ], 401);
        }

        $news->setAuthor($user);

        $errors = $validator->validate($news);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        $newsRepository->save($news, true);

        return $this->json($news, 201, [], ['groups' => ['news:read', 'user:read']]);
    }

    #[Route('/api/news/{id}', name: 'api_news_edit', methods: ['PUT'])]
    public function edit(
        int $id,
        Request $request,
        NewsRepository $newsRepository,
        ValidatorInterface $validator
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_REDACTOR');

        $news = $newsRepository->find($id);
        if (!$news) {
            return $this->json([
                'message' => 'News not found',
            ], 404);
        }

        $data = $this->decodeJsonObject($request);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $this->hydrateNews($news, $data);

        $errors = $validator->validate($news);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        $newsRepository->save($news, true);

        return $this->json($news, 200, [], ['groups' => ['news:read', 'user:read']]);
    }

    #[Route('/api/news/{id}', name: 'api_news_delete', methods: ['DELETE'])]
    public function delete(int $id, NewsRepository $newsRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $news = $newsRepository->find($id);
        if (!$news) {
            return $this->json([
                'message' => 'News not found',
            ], 404);
        }

        $newsRepository->remove($news, true);

        return $this->json([
            'message' => 'News deleted successfully',
        ], 200);
    }

    /**
     * @return array<string, mixed>|JsonResponse
     */
    private function decodeJsonObject(Request $request): array|JsonResponse
    {
        $raw = $request->getContent();

        if ($raw === '' || $raw === null) {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return $this->json([
                'message' => 'Invalid JSON',
            ], 400);
        }

        if (!is_array($decoded)) {
            return $this->json([
                'message' => 'Invalid JSON body',
            ], 400);
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hydrateNews(News $news, array $data): void
    {
        if (array_key_exists('title', $data)) {
            $news->setTitle((string) $data['title']);
        }

        if (array_key_exists('content', $data)) {
            $news->setContent((string) $data['content']);
        }

        if (array_key_exists('network', $data)) {
            $news->setNetwork((string) $data['network']);
        }

        if (array_key_exists('line', $data)) {
            $news->setLine((string) $data['line']);
        }

        if (array_key_exists('type', $data)) {
            $news->setType((string) $data['type']);
        }
    }

    private function validationErrorResponse(iterable $errors): JsonResponse
    {
        $formatted = [];

        foreach ($errors as $error) {
            $formatted[] = [
                'field' => (string) $error->getPropertyPath(),
                'message' => (string) $error->getMessage(),
            ];
        }

        return $this->json([
            'message' => 'Validation failed',
            'errors' => $formatted,
        ], 422);
    }
}