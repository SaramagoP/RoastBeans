<?php

namespace App\DataFixtures;

use DateTimeImmutable;
use App\Entity\Settings;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class SettingsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $settings = $this->createSettings();

        $manager->persist($settings);

        $manager->flush();
    }

    private function createSettings(): Settings
    {
        $settings = new Settings();

        $settings->setWebsiteName("RoastBeans");
        $settings->setWebsiteUrl("http://roastbeans.com");
        $settings->setDescription("Nous nous engageons à vous offrir des cafés de qualité supérieure, torréfiés à la perfection.");
        $settings->setEmail("roastbeans@gmail.com");
        $settings->setPhone("+33 (0)1 23 45 67 89");
        $settings->setAdresse(" 123 Rue du Café");
        $settings->setCity("Paris");
        $settings->setCountry("France");
        $settings->setPostalCode("75001");
        $settings->setCreatedAt(new DateTimeImmutable ());
        $settings->setUpdatedAt(new DateTimeImmutable ());
            
        return $settings;
    }
}
