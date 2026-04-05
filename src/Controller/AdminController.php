<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\NewsRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function index(
        UserRepository $userRepository,
        NewsRepository $newsRepository,
        CommentRepository $commentRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $usersCount = $userRepository->count([]);
        $newsCount = $newsRepository->count([]);
        $commentsCount = $commentRepository->count([]);

        $topNews = $newsRepository->findBy([], ['id' => 'DESC'], 5);

        return $this->render('admin/index.html.twig', [
            'usersCount' => $usersCount,
            'newsCount' => $newsCount,
            'commentsCount' => $commentsCount,
            'topNews' => $topNews,
        ]);
    }
}