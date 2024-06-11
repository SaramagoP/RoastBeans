<?php

namespace App\Controller\User\Home;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
class HomeController extends AbstractController
{
    // Restriction d'accès aux utilisateurs avec le rôle 'ROLE_USER'
    #[IsGranted('ROLE_USER')]
    // Définition de la route pour l'index de la page d'accueil utilisateur
    #[Route('/home', name: 'user_home_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response  // Injection des dépendances pour accéder aux commandes
    {
        // Récupération de l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Recherche des commandes de l'utilisateur connecté
        $orders = $orderRepository->findBy(['user' => $user]);

        // Rendu du template Twig avec les données récupérées
        return $this->render('pages/user/home/index.html.twig', [
            'orders' => $orders // Passage des commandes de l'utilisateur au template
        ]);
    }
}