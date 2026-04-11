<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/news')]
#[IsGranted('ROLE_ADMIN')]
class AdminNewsController extends AbstractController
{
    #[Route('/create', name: 'app_admin_news_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $news = new News();
            $news->setTitle($request->request->get('title', ''));
            $news->setContent($request->request->get('content', ''));
            $news->setNetwork($request->request->get('network', 'metro'));
            $news->setLine($request->request->get('line', ''));
            $news->setType($request->request->get('type', 'info'));
            $news->setSource($request->request->get('source', 'official'));
            $news->setPublishedAt(new \DateTimeImmutable());
            $news->setAuthor($this->getUser());
            $em->persist($news);
            $em->flush();
            $this->addFlash('success', 'Actualité créée !');
            return $this->redirectToRoute('app_news');
        }

        return $this->render('admin/news/form.html.twig', [
            'news' => null,
            'title' => 'Créer une actualité',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_news_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, NewsRepository $repo, EntityManagerInterface $em): Response
    {
        $news = $repo->find($id);
        if (!$news) throw $this->createNotFoundException();

        if ($request->isMethod('POST')) {
            $news->setTitle($request->request->get('title', ''));
            $news->setContent($request->request->get('content', ''));
            $news->setNetwork($request->request->get('network', 'metro'));
            $news->setLine($request->request->get('line', ''));
            $news->setType($request->request->get('type', 'info'));
            $news->setSource($request->request->get('source', 'official'));
            $em->flush();
            $this->addFlash('success', 'Actualité modifiée !');
            return $this->redirectToRoute('app_news_show', ['id' => $id]);
        }

        return $this->render('admin/news/form.html.twig', [
            'news'  => $news,
            'title' => 'Modifier une actualité',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_news_delete', methods: ['POST'])]
    public function delete(int $id, NewsRepository $repo, EntityManagerInterface $em): Response
    {
        $news = $repo->find($id);
        if ($news) {
            $em->remove($news);
            $em->flush();
            $this->addFlash('success', 'Actualité supprimée !');
        }
        return $this->redirectToRoute('app_news');
    }
}