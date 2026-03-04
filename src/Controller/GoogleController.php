<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectAction(ClientRegistry $clientRegistry): \Symfony\Component\HttpFoundation\Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $redirect */
        $redirect = $clientRegistry
            ->getClient('google')
            ->redirect([
                'email', 'profile'
            ], []);
        return $redirect;
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(Request $request): void
    {
        // Cette méthode peut rester vide, elle sera interceptée par l'Authenticator
    }
}
