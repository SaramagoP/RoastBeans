<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    // Le constructeur reçoit un objet UserPasswordHasherInterface pour hacher les mots de passe
    public function __construct(private UserPasswordHasherInterface $hasher) // Faire appel au hasher du mot de passe
    {
    }

    // Méthode principale pour charger les utilisateurs dans la base de données
    public function load(ObjectManager $manager): void
    {
        // Définir les données des utilisateurs à créer
        $usersData = [
            [
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'email' => 'dupont@gmail.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Azerty.12345**'
            ],
            [
                'firstName' => 'Marie',
                'lastName' => 'Curie',
                'email' => 'curiemarie@gmail.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Azerty.4321**'
            ],
            [
                'firstName' => 'Bill',
                'lastName' => 'Gates',
                'email' => 'billgates@gmail.com',
                'roles' => ['ROLE_USER'],
                'password' => 'Azerty.9876**'
            ]
        ];

        // Pour chaque jeu de données utilisateur
        foreach ($usersData as $userData) {
            
            // Crée un nouvel utilisateur en utilisant les données fournies
            $user = $this->createUser($userData);

            // Persiste (sauvegarde) l'utilisateur dans le gestionnaire d'entités
            $manager->persist($user);
        }

        // Enregistre tous les utilisateurs persistés dans la base de données
        $manager->flush();
    }

    /**
     * Crée un utilisateur avec les données fournies et renvoie l'objet User
     *
     * @param array $userData Les données de l'utilisateur à créer
     * @return User
     */
    private function createUser(array $userData): User
    {
        // Crée une nouvelle instance de l'utilisateur
        $user = new User();

        // Hache le mot de passe de l'utilisateur
        $passwordHashed = $this->hasher->hashPassword($user, $userData['password']);

        // Définit les propriétés de l'utilisateur avec les données fournies
        $user
            ->setFirstName($userData['firstName']) 
            ->setLastName($userData['lastName']) 
            ->setEmail($userData['email']) 
            ->setRoles($userData['roles']) 
            ->setPassword($passwordHashed) 
            ->setVerified(true) 
            ->setCreatedAt(new DateTimeImmutable()) 
            ->setUpdatedAt(new DateTimeImmutable()); 

        // Renvoie l'objet utilisateur
        return $user;
    }
}