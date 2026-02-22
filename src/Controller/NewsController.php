<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Annotation\IsGranted;
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
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'details' => ['limit' => ['Must be between 1 and 100']],
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

        return $this->json(
            [
                'meta' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                ],
                'data' => $result['items'],
            ],
            200,
            [],
            ['groups' => ['news:read', 'user:read']]
        );
    }

    #[Route('/api/news/{id}', name: 'api_news_detail', methods: ['GET'])]
    public function show(int $id, NewsRepository $newsRepository): JsonResponse
    {
        $news = $newsRepository->find($id);

        if (!$news) {
            return $this->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'News not found']], 404);
        }

        return $this->json($news, 200, [], ['groups' => ['news:read', 'user:read']]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/news', name: 'api_news_create', methods: ['POST'])]
    public function create(Request $request, NewsRepository $newsRepository, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => ['code' => 'BAD_JSON', 'message' => 'Invalid JSON body']], 400);
        }

        $news = new News();
        if (array_key_exists('title', $data)) $news->setTitle((string) $data['title']);
        if (array_key_exists('content', $data)) $news->setContent((string) $data['content']);
        if (array_key_exists('network', $data)) $news->setNetwork((string) $data['network']);
        if (array_key_exists('line', $data)) $news->setLine((string) $data['line']);
        if (array_key_exists('type', $data)) $news->setType((string) $data['type']);

        $news->setPublishedAt(new \DateTimeImmutable());

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => ['code' => 'UNAUTHORIZED', 'message' => 'You must be logged in']], 401);
        }
        $news->setAuthor($user);

        $errors = $validator->validate($news);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        $newsRepository->save($news, true);

        return $this->json($news, 201, [], ['groups' => ['news:read', 'user:read']]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/news/{id}', name: 'api_news_edit', methods: ['PUT'])]
    public function edit(int $id, Request $request, NewsRepository $newsRepository, ValidatorInterface $validator): JsonResponse
    {
        $news = $newsRepository->find($id);
        if (!$news) {
            return $this->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'News not found']], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => ['code' => 'BAD_JSON', 'message' => 'Invalid JSON body']], 400);
        }

        if (array_key_exists('title', $data)) $news->setTitle((string) $data['title']);
        if (array_key_exists('content', $data)) $news->setContent((string) $data['content']);
        if (array_key_exists('network', $data)) $news->setNetwork((string) $data['network']);
        if (array_key_exists('line', $data)) $news->setLine((string) $data['line']);
        if (array_key_exists('type', $data)) $news->setType((string) $data['type']);

        $errors = $validator->validate($news);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        $newsRepository->save($news, true);

        return $this->json($news, 200, [], ['groups' => ['news:read', 'user:read']]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/news/{id}', name: 'api_news_delete', methods: ['DELETE'])]
    public function delete(int $id, NewsRepository $newsRepository): JsonResponse
    {
        $news = $newsRepository->find($id);
        if (!$news) {
            return $this->json(['error' => ['code' => 'NOT_FOUND', 'message' => 'News not found']], 404);
        }

        $newsRepository->remove($news, true);

        return $this->json(['message' => 'News deleted successfully'], 200);
    }

    private function validationErrorResponse(iterable $errors): JsonResponse
    {
        $details = [];
        foreach ($errors as $error) {
            $details[$error->getPropertyPath()][] = $error->getMessage();
        }

        return $this->json([
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'details' => $details,
            ],
        ], 422);
    }
}