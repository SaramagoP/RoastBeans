<?php

namespace App\Controller\Visitor\Cart;


use App\Service\Cart\CartService;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository, 
        private CartService $cartService
    )
    {
    }

    // Utiliser la méthode index pour récupérer les données du panier pour voir ce que cela contient concrètement, parce que c'est cette méthode qui me permet d'accéder à la page sur laquelle je veux afficher les informations 
    #[Route('/cart', name: 'visitor_cart_index', methods: ['GET'])]
    public function index(): Response
    {
        // $this->cartService->getCartItems(); // Donc je fais appel à cartService et je lui demande tout simplement de me récupérer le panier

        return $this->render('pages/visitor/cart/index.html.twig', [
            'cartItems'     => $this->cartService->getCartItems(),
            'cartTotalAmount'   => $this->cartService->getCartTotalAmount()
        ]);
    }


    #[Route('/cart/{id}/add', name: 'visitor_add_cart', methods: ['GET'])]
    public function add(string $id): Response // On met $id au lieux $product pour poivoir faire des traitements particuliers, comme par exemple me assurer que le produit existe dans la base de données
    {
        // Recherche du produit dans la base de données en fonction de son identifiant $id
        $product = $this->productRepository->find((int) $id);

        // Vérification si le produit existe
        if (null === $product) 
        {
            // Si le produit n'est pas trouvé, une exception 404 est levée
            throw $this->createNotFoundException("Le produit avec id={$id} est introuvable.");
        }

        // Vérification si la quantité du produit est supérieure à zéro
        if ($product->getQuantity() <= 0) 
        {
            // Si la quantité est nulle ou négative, le produit est considéré comme indisponible
            throw $this->createNotFoundException("Le produit avec id={$id} n'est pas disponible.");
        }

        // Appel du service de gestion du panier pour ajouter une unité du produit
        $this->cartService->add((int)$id);

        // Redirection vers la page du panier après l'ajout du produit
        return $this->redirectToRoute("visitor_cart_index");
    }
 
    
    #[Route('/cart/{id}/decrement', name: 'visitor_decrement_cart', methods: ['GET'])]
    public function decrement(string $id): Response
    {
        // Recherche du produit dans la base de données en fonction de son identifiant $id
        $product = $this->productRepository->find((int) $id);

        // Vérification si la quantité du produit est supérieure à zéro
        if (null === $product) 
        {
            // Si la quantité est nulle ou négative, le produit est considéré comme indisponible
            throw $this->createNotFoundException("Le produit avec id={$id} est introuvable.");
        }

        // Vérification si la quantité du produit est supérieure à zéro
        if ($product->getQuantity() <= 0) 
        {
            // Si la quantité est nulle ou négative, le produit est considéré comme indisponible
            throw $this->createNotFoundException("Le produit avec id={$id} n'est pas disponible.");
        }

        // Appel du service de gestion du panier pour décrémenter une unité du produit
        $this->cartService->decrement((int)$id);

        // Redirection vers la page du panier après le décrément du produit
        return $this->redirectToRoute("visitor_cart_index");
    }


    #[Route('/cart/{id}/remove', name: 'visitor_remove_cart', methods: ['GET'])]
    public function remove(string $id): Response
    {
        // Recherche du produit dans la base de données en fonction de son identifiant $id
        $product = $this->productRepository->find((int) $id);

        // Vérification si le produit existe
        if (null === $product) 
        {
            // Si le produit n'est pas trouvé, une exception 404 est levée
            throw $this->createNotFoundException("Le produit avec id={$id} est introuvable.");
        }

        // Vérification si la quantité du produit est supérieure à zéro
        if ($product->getQuantity() <= 0) 
        {
            // Si la quantité est nulle ou négative, le produit est considéré comme indisponible
            throw $this->createNotFoundException("Le produit avec id={$id} n'est pas disponible.");
        }

        // Appel du service de gestion du panier pour supprimer complètement le produit du panier
        $this->cartService->remove((int)$id);

        // Redirection vers la page du panier après la suppression du produit
        return $this->redirectToRoute("visitor_cart_index");
    }
}
