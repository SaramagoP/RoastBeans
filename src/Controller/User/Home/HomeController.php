<?php

namespace App\Controller\User\Home;


use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/user')] // Déclare que cette classe va gérer les routes commençant par "/user"
class HomeController extends AbstractController // Déclare la classe HomeController qui hérite de AbstractController
{
    #[IsGranted('ROLE_USER')] // Indique que l'utilisateur doit avoir le rôle ROLE_USER pour accéder aux actions de cette classe
    #[Route('/home', name: 'user_home_index', methods: ['GET'])] // Définit une route '/home' accessible via une requête GET et nommée 'user_home_index'
    public function index( // Déclare la méthode index
        CategoryRepository $categoryRepository, // Injecte le repository des catégories
        ProductRepository $productRepository // Injecte le repository des produits
    ): Response // La méthode renvoie une réponse HTTP
    {
        $categories = $categoryRepository->findAll(); // Récupère toutes les catégories
        $products = $productRepository->findAll(); // Récupère tous les produits

        return $this->render('pages/visitor/catalog/index.html.twig', [ // Rend le template Twig 'index.html.twig'
            'categories' => $categories, // Passe les catégories au template
            'products' => $products // Passe les produits au template
        ]);
    }

    #[IsGranted('ROLE_USER')] // Indique que l'utilisateur doit avoir le rôle ROLE_USER pour accéder à cette action
    #[Route('/recent_order', name: 'user_recent_order_index', methods: ['GET'])] // Définit une route '/recent_order' accessible via une requête GET et nommée 'user_recent_order_index'
    public function order(OrderRepository $orderRepository): Response // Déclare la méthode order
    {
        $user = $this->getUser(); // Récupère l'utilisateur actuellement connecté
        $orders = $orderRepository->findBy(['user' => $user]); // Récupère les commandes de l'utilisateur

        return $this->render('pages/user/home/index.html.twig', [ // Rend le template Twig 'index.html.twig'
            'orders' => $orders // Passe les commandes au template
        ]);
    }
}