<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlaceholderController extends AbstractController
{
    #[Route('/news', name: 'app_news')]
    public function news(): Response
    {
        return $this->render('news/index.html.twig');
    }

    #[Route('/news/{id}', name: 'app_news_show')]
public function newsShow(int $id): Response
{
    return $this->render('news/show.html.twig', [
        'newsId' => $id,
        'metaTitle' => 'Actualité #' . $id . ' - VeGlobs',
        'metaDesc'  => 'Détail de l\'actualité transport sur VeGlobs Paris.',
    ]);
}

        #[Route('/journey', name: 'app_journey')]
        public function journey(): Response
        {
    return $this->render('pages/journey.html.twig');
    }
    #[Route('/transport', name: 'app_transport')]
    public function transport(): Response
    {
        return $this->render('transport/index.html.twig');
    }
        #[Route('/velib', name: 'app_velib')]
    public function velib(): Response
    {
        return $this->render('velib/index.html.twig');
    }
    #[Route('/login', name: 'app_login')]
    
    public function login(): Response
    {
        return $this->render('security/login.html.twig');
    }
    #[Route('/mes-lignes', name: 'app_follow')]
public function follow(): Response
{
    return $this->render('follow/index.html.twig');
}


    #[Route('/register', name: 'app_register')]
    public function register(): Response
    {
        return new Response('Page Register à venir');
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        return new Response('Page Profil à venir');
    }

    }