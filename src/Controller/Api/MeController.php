<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class MeController extends AbstractController
{
    #[Route('/me', name: 'api_me_get', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'data' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'birthDate' => $user->getBirthDate()?->format('Y-m-d'),
                'isVerified' => $user->isVerified(),
                'roles' => $user->getRoles(),
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/me', name: 'api_me_update', methods: ['PUT'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $payload = json_decode($request->getContent() ?: '{}', true);
        if (!is_array($payload)) {
            return $this->json([
                'errors' => [['message' => 'Invalid JSON payload.']],
            ], Response::HTTP_BAD_REQUEST);
        }

        if (array_key_exists('firstName', $payload)) {
            $user->setFirstName((string) $payload['firstName']);
        }
        if (array_key_exists('lastName', $payload)) {
            $user->setLastName((string) $payload['lastName']);
        }
        if (array_key_exists('birthDate', $payload)) {
            try {
                $user->setBirthDate(new \DateTimeImmutable((string) $payload['birthDate']));
            } catch (\Throwable $e) {
                return $this->json([
                    'errors' => [[
                        'field' => 'birthDate',
                        'message' => 'Invalid date format. Use YYYY-MM-DD.',
                    ]],
                ], 422);
            }
        }

        // Validation entity (NotBlank/Length/etc)
        $errors = $validator->validate($user);
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

        return $this->json([
            'data' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'birthDate' => $user->getBirthDate()?->format('Y-m-d'),
                'isVerified' => $user->isVerified(),
                'roles' => $user->getRoles(),
            ],
        ], Response::HTTP_OK);
    }

    #[Route('/me/password', name: 'api_me_password', methods: ['PUT'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $payload = json_decode($request->getContent() ?: '{}', true);
        if (!is_array($payload)) {
            return $this->json([
                'errors' => [['message' => 'Invalid JSON payload.']],
            ], Response::HTTP_BAD_REQUEST);
        }

        $currentPassword = (string) ($payload['currentPassword'] ?? '');
        $newPassword = (string) ($payload['newPassword'] ?? '');

        if ($currentPassword === '' || $newPassword === '') {
            return $this->json([
                'errors' => [[
                    'message' => 'currentPassword and newPassword are required.'
                ]],
            ], 422);
        }

        if (!$hasher->isPasswordValid($user, $currentPassword)) {
            return $this->json([
                'errors' => [[
                    'field' => 'currentPassword',
                    'message' => 'Current password is incorrect.'
                ]],
            ], 422);
        }

        if (mb_strlen($newPassword) < 6) {
            return $this->json([
                'errors' => [[
                    'field' => 'newPassword',
                    'message' => 'New password must be at least 6 characters.'
                ]],
            ], 422);
        }

        $user->setPassword($hasher->hashPassword($user, $newPassword));
        $em->flush();

        return $this->json([
            'data' => ['message' => 'Password updated successfully.']
        ], Response::HTTP_OK);
    }
}