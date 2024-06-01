<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SuperAdminFixtures extends Fixture // La classe SuperAdminFixtures étend la classe Fixture pour charger les données initiales pour le super administrateur.
{
    public function __construct(private UserPasswordHasherInterface $hasher) // Injection de dépendance pour le service de hachage de mot de passe.
    {
    }

    public function load(ObjectManager $manager): void // La méthode load est utilisée pour charger les données dans la base de données.
    {
        $superAdmin = $this->createSuperAdmin(); // Création d'un objet SuperAdmin avec des valeurs prédéfinies.

        $manager->persist($superAdmin); // Persist l'objet superAdmin pour le rendre géré par l'EntityManager.

        $manager->flush(); // Envoie les modifications en base de données.
    }

    /**
     * Le rôle de cette méthode est de créer le super admin
     *
     * @return User
     */
    private function createSuperAdmin(): User // Méthode privée pour créer et retourner un objet User avec des valeurs prédéfinies.
    {
        $superAdmin = new User(); // Création d'une nouvelle instance de User.

        $passwordHashed = $this->hasher->hashPassword($superAdmin, "Azerty.1234**"); // Hachage du mot de passe pour le super administrateur.

        $superAdmin
            ->setFirstName("Pierre") // Définition du prénom.
            ->setLastName("Dubois") // Définition du nom de famille.
            ->setEmail("roastbeans@gmail.com") // Définition de l'adresse email.
            ->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER']) // Définition des rôles de l'utilisateur.
            ->setPassword($passwordHashed) // Définition du mot de passe haché.
            ->setVerified(true) // Définition du statut de vérification de l'utilisateur.
            ->setCreatedAt(new DateTimeImmutable()) // Définition de la date de création.
            ->setUpdatedAt(new DateTimeImmutable()); // Définition de la date de mise à jour.

        return $superAdmin; // Retourne l'objet superAdmin créé.
    }
}
