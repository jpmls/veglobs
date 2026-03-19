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
        return new Response('Page News à venir');
    }

    #[Route('/journey', name: 'app_journey')]
    public function journey(): Response
    {
        return new Response('Page Journey à venir');
    }

    #[Route('/transport', name: 'app_transport')]
    public function transport(): Response
    {
        return new Response('Page Transport à venir');
    }

    #[Route('/velib', name: 'app_velib')]
public function velib(): Response
{
    return $this->render('velib/index.html.twig');
}

    #[Route('/login', name: 'app_login')]
    public function login(): Response
    {
        return new Response('Page Login à venir');
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

    #[Route('/admin', name: 'app_admin')]
    public function admin(): Response
    {
        return new Response('Page Admin à venir');
    }
}