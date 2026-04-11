<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/newsletter')]
#[IsGranted('ROLE_ADMIN')]
class NewsletterController extends AbstractController
{
    #[Route('', name: 'app_admin_newsletter', methods: ['GET', 'POST'])]
    public function index(Request $request, Connection $db, MailerInterface $mailer): Response
    {
        $sent = 0;

        if ($request->isMethod('POST')) {
            $subject = $request->request->get('subject', 'Actualités VeGlobs');
            $body    = $request->request->get('body', '');
            $network = $request->request->get('network', '');

            // Récupérer les utilisateurs abonnés au réseau
            if ($network) {
                $follows = $db->fetchAllAssociative(
                    "SELECT DISTINCT u.email, u.first_name 
                     FROM follow f 
                     JOIN user u ON u.id = f.user_id 
                     WHERE f.network = ?",
                    [$network]
                );
            } else {
                $follows = $db->fetchAllAssociative(
                    "SELECT DISTINCT u.email, u.first_name FROM user u"
                );
            }

            foreach ($follows as $recipient) {
                $email = (new Email())
                    ->from('no-reply@veglobs.local')
                    ->to($recipient['email'])
                    ->subject($subject)
                    ->html("
                        <h2>{$subject}</h2>
                        <p>Bonjour {$recipient['first_name']},</p>
                        <div>{$body}</div>
                        <hr>
                        <p style='color:#6b7280;font-size:12px;'>VeGlobs sur Paris — <a href='http://localhost:8000/news'>Voir les actualités</a></p>
                    ");

                $mailer->send($email);
                $sent++;
            }

            $this->addFlash('success', "{$sent} email(s) envoyé(s) !");
            return $this->redirectToRoute('app_admin_newsletter');
        }

        return $this->render('admin/newsletter.html.twig');
    }
}