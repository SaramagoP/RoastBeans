<?php

namespace App\Controller\Visitor\Authentication;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route(path: '/login', name: 'visitor_authentication_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Vérifier si l'utilisateur est déjà connecté.
        // Si oui, rediriger vers la page d'accueil des visiteurs.
        if ($this->getUser()) 
        {
            // Redirection vers la page d'accueil des visiteurs si déjà connecté.
            return $this->redirectToRoute('visitor_welcome_index');
        }

        // Récupérer l'éventuelle erreur de connexion
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupérer le dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        // Afficher la page de connexion avec le dernier nom d'utilisateur et l'erreur éventuelle
        return $this->render('pages/visitor/authentication/login.html.twig', [
            'last_username' => $lastUsername, 'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut être vide car la déconnexion sera gérée par Symfony via la configuration de sécurité.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
