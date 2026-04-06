<?php

namespace App\Controller\Api;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/follow')]
class FollowController extends AbstractController
{
    /**
     * GET /api/follow — liste des lignes suivies par l'utilisateur
     */
    #[Route('', name: 'api_follow_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(Connection $db): JsonResponse
    {
        $userId = $this->getUser()->getId();

        $follows = $db->fetchAllAssociative(
            "SELECT id, network, line, created_at FROM follow WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );

        return $this->json($follows);
    }

    /**
     * POST /api/follow — suivre une ligne
     * Body: { "network": "metro", "line": "4" }
     */
    #[Route('', name: 'api_follow_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, Connection $db): JsonResponse
    {
        $data    = json_decode($request->getContent(), true);
        $network = trim($data['network'] ?? '');
        $line    = trim($data['line'] ?? '');
        $userId  = $this->getUser()->getId();

        if (!$network) {
            return $this->json(['error' => 'network requis'], 400);
        }

        // Vérifier si déjà suivi
        $existing = $db->fetchOne(
            "SELECT id FROM follow WHERE user_id = ? AND network = ? AND line = ?",
            [$userId, $network, $line]
        );

        if ($existing) {
            return $this->json(['message' => 'Déjà suivi', 'following' => true]);
        }

        $db->executeStatement(
            "INSERT INTO follow (user_id, network, line, created_at) VALUES (?, ?, ?, NOW())",
            [$userId, $network, $line]
        );

        return $this->json(['message' => 'Ligne suivie', 'following' => true], 201);
    }

    /**
     * DELETE /api/follow — ne plus suivre une ligne
     * Body: { "network": "metro", "line": "4" }
     */
    #[Route('', name: 'api_follow_remove', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function remove(Request $request, Connection $db): JsonResponse
    {
        $data    = json_decode($request->getContent(), true);
        $network = trim($data['network'] ?? '');
        $line    = trim($data['line'] ?? '');
        $userId  = $this->getUser()->getId();

        $db->executeStatement(
            "DELETE FROM follow WHERE user_id = ? AND network = ? AND line = ?",
            [$userId, $network, $line]
        );

        return $this->json(['message' => 'Ligne retirée', 'following' => false]);
    }

    /**
     * GET /api/follow/news — news filtrées selon les lignes suivies
     */
    #[Route('/news', name: 'api_follow_news', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function followedNews(Connection $db): JsonResponse
    {
        $userId = $this->getUser()->getId();

        $follows = $db->fetchAllAssociative(
            "SELECT network, line FROM follow WHERE user_id = ?",
            [$userId]
        );

        if (empty($follows)) {
            return $this->json(['data' => [], 'message' => 'Aucune ligne suivie']);
        }

        // Construire la clause WHERE pour filtrer les news
        $conditions = [];
        $params     = [];

        foreach ($follows as $f) {
            if ($f['line']) {
                $conditions[] = "(network = ? AND line = ?)";
                $params[]     = $f['network'];
                $params[]     = $f['line'];
            } else {
                $conditions[] = "network = ?";
                $params[]     = $f['network'];
            }
        }

        $where = implode(' OR ', $conditions);

        $news = $db->fetchAllAssociative(
            "SELECT id, title, content, network, line, type, source, published_at, views
             FROM news
             WHERE ({$where})
             ORDER BY published_at DESC
             LIMIT 20",
            $params
        );

        return $this->json(['data' => $news]);
    }
}