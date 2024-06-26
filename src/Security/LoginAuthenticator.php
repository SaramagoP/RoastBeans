<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'visitor_authentication_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        // 2- Récupérer l'email envoyé par l'utilisateur depuis le formulaire de connexion
        $email = $request->getPayload()->getString('email');

        // 3- Sauvegarder l'email en session
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // 4- Vérifier si l'email et le mot de passe provenant du formulaire correspondent 
            // à un utilisateur existant dans la base de données
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')), // password_verify(string $password, string $hash)
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // 5- Si l'utilisateur n'est pas connu en base de données, 
            // Récupérer l'email précedemment envoyé depuis le formulaire et qui a été sauvegardé en session
            // Effectuer la redirection vers la page de laquelle proviennent les informations
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) 
        {
            return new RedirectResponse($targetPath);
        }

        // 6- Dans le cas contraire,
            // Rediriger l'administrateur vers la'espace d'administration
            // Et l'utilisateur vers la page d'accueil
        
        $user = $token->getUser(); // Récupère l'utilisateur associé au token de sécurité actuel
        $roles = $user->getRoles(); // Récupère les rôles de l'utilisateur
        
        if (\in_array("ROLE_ADMIN", $roles)) { // Vérifie si l'utilisateur a le rôle "ROLE_ADMIN"
            return new RedirectResponse($this->urlGenerator->generate('admin_home')); // Redirige vers la page d'accueil de l'administration
        }
        
        if (\in_array("ROLE_USER", $roles)) { // Vérifie si l'utilisateur a le rôle "ROLE_USER"
            return new RedirectResponse($this->urlGenerator->generate('user_home_index')); // Redirige vers la page d'accueil de l'utilisateur
        }
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
