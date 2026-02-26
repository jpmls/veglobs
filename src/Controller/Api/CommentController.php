<?php

namespace App\Controller\Api;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\NewsRepository;
use App\Security\Voter\CommentVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
#[Route('/api')]
class CommentController extends AbstractController
{
    #[Route('/news/{id}/comments', name: 'api_news_comments_list', methods: ['GET'])]
    public function listForNews(
        int $id,
        Request $request,
        NewsRepository $newsRepository,
        CommentRepository $commentRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        $news = $newsRepository->find($id);
        if (!$news) {
            return $this->json([
                'errors' => [
                    ['message' => 'News not found.'],
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(max(1, (int) $request->query->get('limit', 10)), 50);

        $result = $commentRepository->findByNewsPaginated($id, $page, $limit);

        $json = $serializer->serialize($result['items'], 'json', [
            'groups' => ['comment:read'],
        ]);

        return new JsonResponse([
            'data' => json_decode($json, true, flags: JSON_THROW_ON_ERROR),
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total'],
            ],
        ], Response::HTTP_OK);
    }

    #[Route('/news/{id}/comments', name: 'api_news_comments_create', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function createForNews(
        int $id,
        Request $request,
        NewsRepository $newsRepository,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user || !$user->isVerified()) {
            return $this->json([
                'errors' => [
                    ['message' => 'You must verify your account before commenting.']
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        $news = $newsRepository->find($id);
        if (!$news) {
            return $this->json([
                'errors' => [
                    ['message' => 'News not found.'],
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent() ?: '{}', true);
        if (!is_array($payload)) {
            return $this->json([
                'errors' => [
                    ['message' => 'Invalid JSON payload.'],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $comment = new Comment();
        $comment->setContent((string) ($payload['content'] ?? ''));
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setNews($news);
        $comment->setAuthor($user);

        $errors = $validator->validate($comment);
        if (count($errors) > 0) {
            $formatted = [];
            foreach ($errors as $error) {
                $formatted[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }

            return $this->json([
                'errors' => $formatted,
            ], 422);
        }

        $em->persist($comment);
        $em->flush();

        $json = $serializer->serialize($comment, 'json', [
            'groups' => ['comment:read'],
        ]);

        return new JsonResponse([
            'data' => json_decode($json, true, flags: JSON_THROW_ON_ERROR),
        ], Response::HTTP_CREATED);
    }

    #[Route('/comments/{id}', name: 'api_comments_update', methods: ['PUT'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(
        int $id,
        Request $request,
        CommentRepository $commentRepository,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user || !$user->isVerified()) {
            return $this->json([
                'errors' => [
                    ['message' => 'You must verify your account before updating comments.']
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        $comment = $commentRepository->find($id);
        if (!$comment) {
            return $this->json([
                'errors' => [
                    ['message' => 'Comment not found.'],
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted(CommentVoter::EDIT, $comment);

        $payload = json_decode($request->getContent() ?: '{}', true);
        if (!is_array($payload)) {
            return $this->json([
                'errors' => [
                    ['message' => 'Invalid JSON payload.'],
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        if (array_key_exists('content', $payload)) {
            $comment->setContent((string) $payload['content']);
        }

        $errors = $validator->validate($comment);
        if (count($errors) > 0) {
            $formatted = [];
            foreach ($errors as $error) {
                $formatted[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }

            return $this->json(['errors' => $formatted], 422);
        }

        $em->flush();

        $json = $serializer->serialize($comment, 'json', [
            'groups' => ['comment:read'],
        ]);

        return new JsonResponse([
            'data' => json_decode($json, true, flags: JSON_THROW_ON_ERROR),
        ], Response::HTTP_OK);
    }

    #[Route('/comments/{id}', name: 'api_comments_delete', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(
        int $id,
        CommentRepository $commentRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user || !$user->isVerified()) {
            return $this->json([
                'errors' => [
                    ['message' => 'You must verify your account before deleting comments.']
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        $comment = $commentRepository->find($id);
        if (!$comment) {
            return $this->json([
                'errors' => [
                    ['message' => 'Comment not found.'],
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted(CommentVoter::DELETE, $comment);

        $em->remove($comment);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}