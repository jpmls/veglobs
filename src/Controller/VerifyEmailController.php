<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class VerifyEmailController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier) {}

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): RedirectResponse {
        $id = $request->query->get('id');
        if (!$id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);
        if (!$user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('verify_email_error', $e->getReason());
            return $this->redirectToRoute('app_register');
        }

        // ✅ Notification in-app : compte vérifié
        $notification = (new Notification())
            ->setUser($user)
            ->setType('ACCOUNT_VERIFIED')
            ->setTitle('Compte vérifié ✅')
            ->setMessage('Ton compte est maintenant validé. Tu peux commenter et utiliser toutes les fonctionnalités.');

        $em->persist($notification);
        $em->flush();

        $this->addFlash('success', 'Email vérifié ✅ Tu peux maintenant commenter.');
        return $this->redirectToRoute('app_login');
    }
}