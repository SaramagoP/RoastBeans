<?php

namespace App\Controller\Admin\Home;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')] // Définition de la route de base pour la section admin.
class HomeController extends AbstractController // Extension de la classe AbstractController pour utiliser les fonctionnalités communes du contrôleur.
{
    #[Route('/home', name: 'admin_home', methods: ['GET'])] // Définition de la route pour la page d'accueil de l'admin.
    public function index(): Response // Méthode pour gérer l'affichage de la page d'accueil.
    {
        return $this->render('pages/admin/home/index.html.twig'); // Rendu de la vue pour la page d'accueil.
    }
}
