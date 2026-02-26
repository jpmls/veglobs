<?php

namespace App\Controller\Api;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class NotificationController extends AbstractController
{
    #[Route('/me/notifications', name: 'api_me_notifications_list', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function list(NotificationRepository $repo): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $items = $repo->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->json([
            'data' => $items,
        ], Response::HTTP_OK, [], ['groups' => ['notification:read']]);
    }

    #[Route('/me/notifications/{id}/read', name: 'api_me_notifications_read', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function markAsRead(
        int $id,
        NotificationRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        /** @var Notification|null $notif */
        $notif = $repo->find($id);
        if (!$notif || $notif->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'errors' => [['message' => 'Notification not found.']],
            ], Response::HTTP_NOT_FOUND);
        }

        $notif->setIsRead(true);
        $em->flush();

        return $this->json([
            'data' => $notif,
        ], Response::HTTP_OK, [], ['groups' => ['notification:read']]);
    }
}