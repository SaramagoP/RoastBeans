<?php

namespace App\Security;


use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User)
        {
            return;
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) // Chercher le cas dans quelle il donne false
        {
            return; // Si l'user n'est pas connu, arrêter l'execution du script
        }

        // user account is expired, the user may be notified
        if (!$user->isVerified()) 
        {
            throw new CustomUserMessageAccountStatusException('Veuillez vérifier votre compte par email avant de vous connecter.');
        }
    }
}