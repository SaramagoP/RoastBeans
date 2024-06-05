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
        $product = $this->productRepository->find((int) $id);

        if (null === $product) 
        {
            throw $this->createNotFoundException("The product with id={$id} not found.");
        }

        if ($product->getQuantity() <= 0) 
        {
           throw $this->createNotFoundException("The product with id={$id} is unavailable.");
        }

        $this->cartService->add((int)$id);

       return $this->redirectToRoute("visitor_cart_index");
    }
 
    
    #[Route('/cart/{id}/decrement', name: 'visitor_decrement_cart', methods: ['GET'])]
    public function decrement(string $id): Response
    {
        $product = $this->productRepository->find((int) $id);

        if (null === $product) 
        {
            throw $this->createNotFoundException("The product with id={$id} not found.");
        }

        if ($product->getQuantity() <= 0) 
        {
           throw $this->createNotFoundException("The product with id={$id} is unavailable.");
        }

        $this->cartService->decrement((int)$id);

        return $this->redirectToRoute("visitor_cart_index");
    }


    #[Route('/cart/{id}/remove', name: 'visitor_remove_cart', methods: ['GET'])]
    public function remove(string $id): Response
    {
        $product = $this->productRepository->find((int) $id);

        if (null === $product) 
        {
            throw $this->createNotFoundException("The product with id={$id} not found.");
        }

        if ($product->getQuantity() <= 0) 
        {
           throw $this->createNotFoundException("The product with id={$id} is unavailable.");
        }

        $this->cartService->remove((int)$id);

        return $this->redirectToRoute("visitor_cart_index");
    }
}
