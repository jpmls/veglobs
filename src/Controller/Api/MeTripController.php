<?php

namespace App\Controller\Api;

use App\Entity\Trip;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me/trips')]
#[IsGranted('ROLE_USER')]
class MeTripController extends AbstractController
{
    #[Route('', name: 'api_me_trips_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $trips = $em->getRepository(Trip::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->json(['data' => $trips], 200, [], ['groups' => ['trip:read']]);
    }

    #[Route('', name: 'api_me_trips_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid JSON'], 400);
        }

        foreach (['fromStation','toStation','network','line'] as $field) {
            if (empty($data[$field])) {
                return $this->json([
                    'message' => 'Validation failed',
                    'errors' => [['field'=>$field,'message'=>"$field is required"]]
                ], 422);
            }
        }

        $trip = (new Trip())
            ->setUser($user)
            ->setFromStation($data['fromStation'])
            ->setToStation($data['toStation'])
            ->setNetwork($data['network'])
            ->setLine($data['line']);

        if (isset($data['payload']) && is_array($data['payload'])) {
            $trip->setPayload($data['payload']);
        }

        $em->persist($trip);
        $em->flush();

        return $this->json(['data'=>$trip],201,[],['groups'=>['trip:read']]);
    }

    #[Route('/{id}', name: 'api_me_trips_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $trip = $em->getRepository(Trip::class)->find($id);

        if (!$trip) {
            return $this->json(['message'=>'Not found'],404);
        }

        if ($trip->getUser()->getId() !== $user->getId()) {
            return $this->json(['message'=>'Forbidden'],403);
        }

        $em->remove($trip);
        $em->flush();

        return $this->json(['status'=>'ok']);
    }
}