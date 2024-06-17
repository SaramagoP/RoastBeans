<?php

namespace App\DataFixtures;

use DateTimeImmutable;
use App\Entity\Settings;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class SettingsFixtures extends Fixture // La classe SettingsFixtures étend la classe Fixture pour charger les données initiales de configuration.
{
    public function load(ObjectManager $manager): void // La méthode load est utilisée pour charger les données dans la base de données.
    {
        $settings = $this->createSettings(); // Création d'un objet Settings avec des valeurs prédéfinies.

        $manager->persist($settings); // Persist l'objet settings pour le rendre géré par l'EntityManager.

        $manager->flush(); // Envoie les modifications en base de données.
    }

    private function createSettings(): Settings // Méthode privée pour créer et retourner un objet Settings avec des valeurs prédéfinies.
    {
        $settings = new Settings(); // Création d'une nouvelle instance de Settings.

        $settings->setWebsiteName("RoastBeans Café"); // Définition du nom du site web.
        $settings->setWebsiteUrl("https://psdww.com/"); // Définition de l'URL du site web.
        $settings->setDescription("Nous nous engageons à vous offrir des cafés de qualité supérieure, torréfiés à la perfection."); // Définition de la description du site.
        $settings->setUser(null);
        $settings->setEmail("roastbeans@gmail.com"); // Définition de l'adresse email de contact.
        $settings->setPhone("+33 (0)1 23 45 67 89"); // Définition du numéro de téléphone de contact.
        $settings->setAdresse("123 Rue du Café"); // Définition de l'adresse physique.
        $settings->setCity("Paris"); // Définition de la ville.
        $settings->setCountry("France"); // Définition du pays.
        $settings->setPostalCode("75001"); // Définition du code postal.
        $settings->setCreatedAt(new DateTimeImmutable()); // Définition de la date de création.
        $settings->setUpdatedAt(new DateTimeImmutable()); // Définition de la date de mise à jour.
            
        return $settings; // Retourne l'objet Settings créé.
    }
}
