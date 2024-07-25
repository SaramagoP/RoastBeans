<?php

namespace App\Security;


use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserChecker
 * 
 * Cette classe implémente l'interface UserCheckerInterface pour vérifier les utilisateurs
 * avant et après l'authentification.
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * Vérifie les utilisateurs avant l'authentification.
     * 
     * @param UserInterface $user L'utilisateur à vérifier.
     * 
     * @return void
     */
    public function checkPreAuth(UserInterface $user): void
    {
        // Vérifie si l'utilisateur est une instance de la classe User
        if (!$user instanceof User)
        {
            // Si ce n'est pas un utilisateur, ne rien faire et retourner
            return;
        }
    }

    /**
     * Vérifie les utilisateurs après l'authentification.
     * 
     * @param UserInterface $user L'utilisateur à vérifier.
     * 
     * @return void
     * 
     * @throws CustomUserMessageAccountStatusException Si l'utilisateur n'est pas vérifié.
     */
    public function checkPostAuth(UserInterface $user): void
    {
        // Vérifie si l'utilisateur est une instance de la classe User
        if (!$user instanceof User) // Chercher le cas dans quelle il donne false
        {
            return; // Si l'user n'est pas connu, arrêter l'execution du script
        }

        // Vérifie si l'utilisateur a bien vérifié son compte par email
        if (!$user->isVerified()) 
        {
            // Si l'utilisateur n'est pas vérifié, lancer une exception avec un message spécifique
            throw new CustomUserMessageAccountStatusException('Veuillez vérifier votre compte par email avant de vous connecter.');
        }
    }
}

// La classe UserChecker implémente UserCheckerInterface pour ajouter des vérifications personnalisées avant et après l'authentification des utilisateurs. La vérification après l'authentification est utilisée pour s'assurer que l'utilisateur a vérifié son compte par email avant de permettre l'accès à l'application. 