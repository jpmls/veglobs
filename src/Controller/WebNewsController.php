<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\News;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebNewsController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function index(Request $request, NewsRepository $repo): Response
    {
        $filters = [
            'network' => $request->query->get('network'),
            'type'    => $request->query->get('type'),
            'source'  => $request->query->get('source'),
            'q'       => $request->query->get('q'),
        ];
        $page   = max(1, (int) $request->query->get('page', 1));
        $result = $repo->search($filters, $page, 20);

        return $this->render('news/index.html.twig', [
            'news'    => $result['items'],
            'total'   => $result['total'],
            'page'    => $page,
            'pages'   => (int) max(1, ceil($result['total'] / 20)),
            'filters' => $filters,
        ]);
    }

    #[Route('/news/{id}', name: 'app_news_show')]
    public function show(int $id, Request $request, NewsRepository $repo, EntityManagerInterface $em): Response
    {
        $news = $repo->find($id);
        if (!$news) {
            throw $this->createNotFoundException('Actualité introuvable.');
        }

        $news->incrementViews();
        $em->flush();

        if ($request->isMethod('POST') && $this->getUser()) {
            // Signalement communautaire
            if ($request->request->get('report') === '1') {
                $content = trim($request->request->get('content', ''));
                if ($content !== '') {
                    $report = new News();
                    $report->setTitle('Signalement');
                    $report->setContent($content);
                    $report->setType($request->request->get('type', 'incident'));
                    $report->setNetwork($news->getNetwork());
                    $report->setLine($news->getLine() ?? '');
                    $report->setSource('community');
                    $report->setPublishedAt(new \DateTimeImmutable());
                    $report->setAuthor($this->getUser());
                    $em->persist($report);
                    $em->flush();
                    $this->addFlash('success', 'Signalement envoyé !');
                }
                return $this->redirectToRoute('app_news_show', ['id' => $id]);
            }

            // Commentaire
            $content = trim($request->request->get('content', ''));
            if ($content !== '') {
                $comment = new Comment();
                $comment->setContent($content);
                $comment->setNews($news);
                $comment->setAuthor($this->getUser());
                $comment->setCreatedAt(new \DateTimeImmutable());
                $em->persist($comment);
                $em->flush();
                $this->addFlash('success', 'Commentaire ajouté !');
            }
            return $this->redirectToRoute('app_news_show', ['id' => $id]);
        }

        return $this->render('news/show.html.twig', ['news' => $news]);
    }
}