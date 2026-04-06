<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(Connection $db): Response
    {
        // ── Stats news ──
        $totalNews = $db->fetchOne("SELECT COUNT(*) FROM news");
        $newsToday = $db->fetchOne("SELECT COUNT(*) FROM news WHERE DATE(published_at) = CURDATE()");
        $newsByType = $db->fetchAllAssociative("SELECT type, COUNT(*) as count FROM news GROUP BY type ORDER BY count DESC");
        $topNews = $db->fetchAllAssociative("SELECT title, content, views, published_at FROM news ORDER BY views DESC LIMIT 5");
        $newsLast7 = $db->fetchAllAssociative("
            SELECT DATE(published_at) as day, COUNT(*) as count 
            FROM news 
            WHERE published_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(published_at)
            ORDER BY day ASC
        ");

        // ── Stats commentaires ──
        $totalComments = $db->fetchOne("SELECT COUNT(*) FROM comment");
        $commentsToday = $db->fetchOne("SELECT COUNT(*) FROM comment WHERE DATE(created_at) = CURDATE()");

        // ── Stats utilisateurs ──
        $totalUsers = $db->fetchOne("SELECT COUNT(*) FROM user");
        $newUsers   = $db->fetchOne("SELECT COUNT(*) FROM user WHERE DATE(created_at) = CURDATE()");
        $userRoles  = $db->fetchAllAssociative("SELECT roles, COUNT(*) as count FROM user GROUP BY roles");

        // ── Derniers signalements (news communautaires) ──
        $communityNews = $db->fetchAllAssociative("
            SELECT n.title, n.content, n.type, n.published_at, u.email as author
            FROM news n
            LEFT JOIN user u ON u.id = n.author_id
            WHERE n.source = 'community'
            ORDER BY n.published_at DESC
            LIMIT 10
        ");

        return $this->render('admin/index.html.twig', [
            'totalNews'      => $totalNews,
            'newsToday'      => $newsToday,
            'newsByType'     => $newsByType,
            'topNews'        => $topNews,
            'newsLast7'      => $newsLast7,
            'totalComments'  => $totalComments,
            'commentsToday'  => $commentsToday,
            'totalUsers'     => $totalUsers,
            'newUsers'       => $newUsers,
            'userRoles'      => $userRoles,
            'communityNews'  => $communityNews,
        ]);
    }
}