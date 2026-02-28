<?php

namespace App\Controller\Api;

use App\Entity\Follow;
use App\Entity\Notification;
use App\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class FollowController extends AbstractController
{
    #[Route('/me/follows', name: 'api_me_follows_list', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function list(Request $request, FollowRepository $repo): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(max(1, (int) $request->query->get('limit', 10)), 50);

        // pagination simple (sans méthode custom)
        $offset = ($page - 1) * $limit;

        $items = $repo->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('f.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $total = (int) $repo->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json([
            'data' => $items,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
            ],
        ], Response::HTTP_OK, [], ['groups' => ['follow:read']]);
    }

    #[Route('/me/follows', name: 'api_me_follows_create', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(
        Request $request,
        FollowRepository $repo,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // ✅ bloquer si pas vérifié
        if (!$user->isVerified()) {
            return $this->json([
                'errors' => [[
                    'message' => 'Account not verified. Please verify your email before following lines.'
                ]],
            ], Response::HTTP_FORBIDDEN);
        }

        $payload = json_decode($request->getContent() ?: '{}', true);
        if (!is_array($payload)) {
            return $this->json([
                'errors' => [['message' => 'Invalid JSON payload.']],
            ], Response::HTTP_BAD_REQUEST);
        }

        $network = trim((string) ($payload['network'] ?? ''));
        $line = trim((string) ($payload['line'] ?? ''));

        $follow = new Follow();
        $follow->setUser($user);
        $follow->setNetwork($network);
        $follow->setLine($line);

        // ✅ validation 422
        $errors = $validator->validate($follow);
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

        // ✅ anti doublon (check DB)
        $existing = $repo->findOneBy([
            'user' => $user,
            'network' => $network,
            'line' => $line,
        ]);

        if ($existing) {
            return $this->json([
                'errors' => [[
                    'message' => 'Already followed.',
                    'details' => ['network' => $network, 'line' => $line],
                ]],
            ], Response::HTTP_CONFLICT);
        }

        $em->persist($follow);

        // ✅ notification “follow ajouté”
        $notif = new Notification();
        $notif->setUser($user);
        $notif->setType('FOLLOW_ADDED');
        $notif->setTitle('Suivi ajouté ✅');
        $notif->setMessage(sprintf('Tu suis maintenant %s %s.', $network, $line));
        $notif->setPayload(['network' => $network, 'line' => $line]);
        $em->persist($notif);

        $em->flush();

        return $this->json([
            'data' => $follow,
        ], Response::HTTP_CREATED, [], ['groups' => ['follow:read']]);
    }

    #[Route('/me/follows/{id}', name: 'api_me_follows_delete', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(int $id, FollowRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $follow = $repo->find($id);
        if (!$follow || $follow->getUser()?->getId() !== $user->getId()) {
            return $this->json([
                'errors' => [['message' => 'Follow not found.']],
            ], Response::HTTP_NOT_FOUND);
        }

        $em->remove($follow);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}