<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('', name: 'api_admin_users_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $users = $em->getRepository(User::class)->findBy([], ['id' => 'DESC']);

        $data = array_map(function (User $user) {
            return $this->serializeUser($user);
        }, $users);

        return $this->json([
            'data' => $data,
        ], 200);
    }

    #[Route('/{id}/role', name: 'api_admin_users_role_update', methods: ['PUT'])]
    public function updateRole(
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json([
                'message' => 'User not found',
            ], 404);
        }

        $payload = $this->decodeJsonObject($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $roles = $payload['roles'] ?? null;

        if (!is_array($roles) || empty($roles)) {
            return $this->json([
                'message' => 'Validation failed',
                'errors' => [[
                    'field' => 'roles',
                    'message' => 'roles must be a non-empty array',
                ]],
            ], 422);
        }

        $allowedRoles = ['ROLE_USER', 'ROLE_REDACTOR', 'ROLE_ADMIN'];
        $cleanRoles = [];

        foreach ($roles as $role) {
            $role = strtoupper(trim((string) $role));

            if (!in_array($role, $allowedRoles, true)) {
                return $this->json([
                    'message' => 'Validation failed',
                    'errors' => [[
                        'field' => 'roles',
                        'message' => sprintf('Invalid role: %s', $role),
                    ]],
                ], 422);
            }

            $cleanRoles[] = $role;
        }

        $cleanRoles = array_values(array_unique($cleanRoles));

        // sécurité minimale : ROLE_USER toujours présent
        if (!in_array('ROLE_USER', $cleanRoles, true)) {
            $cleanRoles[] = 'ROLE_USER';
        }

        $user->setRoles($cleanRoles);
        $em->flush();

        return $this->json([
            'data' => $this->serializeUser($user),
        ], 200);
    }

    #[Route('/{id}', name: 'api_admin_users_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json([
                'message' => 'User not found',
            ], 404);
        }

        if ($currentUser && $user->getId() === $currentUser->getId()) {
            return $this->json([
                'message' => 'Validation failed',
                'errors' => [[
                    'field' => 'id',
                    'message' => 'You cannot delete your own account',
                ]],
            ], 422);
        }

        $em->remove($user);
        $em->flush();

        return $this->json([
            'status' => 'ok',
        ], 200);
    }

    /**
     * @return array<string,mixed>|JsonResponse
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
     * @return array<string,mixed>
     */
    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'birthDate' => $user->getBirthDate()?->format('Y-m-d'),
            'isVerified' => $user->isVerified(),
            'roles' => $user->getRoles(),
            'createdAt' => method_exists($user, 'getCreatedAt')
                ? $user->getCreatedAt()?->format(\DateTimeInterface::ATOM)
                : null,
        ];
    }
}