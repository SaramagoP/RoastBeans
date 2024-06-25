<?php

namespace App\Service\Cart;


use App\Service\Cart\CartItem;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    // Pour rajouter des données au panier on utilise les Sessions, donc via le mécanisme d'injection des dépendences : "public function __construct", j'injecte le RequestStack que je définis en tant que propriété, ça me permet de récupérer les données de la requête en cours.
    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository
    )
    {
    }

    public function getCart() : array
    {
        // Donc je fais appele à requestStack et je lui dis que je veux récupérér dans les sessions la valeur associée à la clé cart, si tu trouves trés bien, dans le cas contraire tu vas me retourner un tableau vide par défault comme ça ça me permet d'éviter d'avoir des erreurs. Comme ça dans le service : "cartService" à partir de maintenant des que j'aurais besoin du panier tout ce que j'aurai à faire c'est d'appeler cette méthode : "public function getCart() : array"
        return $this->requestStack->getSession()->get('cart', []);
    }

    // En dernier il faut mettre à jour le panier, parce que jai récupérer les données de la session (getSession), j'ai travaillé du coup sur $cart, mais je dois mettre à jour justement les données dans la session et pour ça il faut créer la méthode setCart. Donc pour mofiier le panier je vais recevoir les données du nouveau panier et ensuite je récupére le panier et j'utilise la méthode set en le passant $cart
    public function setCart(array $cart) : self
    {
        $this->requestStack->getSession()->set('cart', $cart);
        return $this;
    }

    public function add(int $id) : void // Et dans cette méthode j'ai besoin de récupérer le panier
    {
        // Donc je récupére le panier en feson appele à getCart(), que je vais sauvegarder dans une variable : $cart
        $cart = $this->getCart();

        // Si le produit que je tente d'ajouter au panier existe déjà 
        // Dans un premier temps je vérifie est-ce que la clé qui correspond l'indentifiant du produit existe dans le tableau ou pas, \array_key_exists c'est la fonction naive de PHP qui permet de vérifier si une clé existe dans un tableau ou pas. D'abord de préciser la clé qui correspond à $id et ensuite préciser le tableau dans lequel on recherche la clé en l'occurrence le panier ($cart)
        if ( \array_key_exists($id, $cart) )  
        {
            // Donc tout ce que j'ai à faire c'est d'appeler le panier et de lui dire de récupérer la valeur associée à la clé $id que j'incrément de 1 car on parle de la quantité du même produit dans le panier
            $cart[$id]++;
        }
        else // Dans le cas contraire le produit n'existe pas dans le panier du coup initialise la valeur à 1 puisqu'on le rajoute la première fois
        {
            $cart[$id] = 1;
        }

        $this->setCart($cart); // Mets à jour le panier
    }


    public function getCartItems() : array
    {
        // Récupérer le panier
        $cart = $this->getCart();
        
        $cartItems = [];

        // En parcourant le tableau du panier, 
            // Récupérons chaque produit correspondant à son identifiant
                // Puis, sauvegardons ce produit ainsi que sa quantité dans le tableau des items
        foreach ($cart as $id => $quantity)
        {
            $product = $this->productRepository->find($id);

            if (null === $product) 
            {
                continue;
            }

            $cartItems[] = new CartItem($product, $quantity);
        }

        return $cartItems;
    }

    public function getCartTotalAmount(): float
    {
        $cartItems = $this->getCartItems();

        $totalAmount = 0;

        foreach ($cartItems as $cartItem)
        {
            $totalAmount += $cartItem->getAmount();
        }

        return $totalAmount;
    }


    //Méthode qu'est censé de recevoir un entier correspondant à l'identifiant, et ça retourne rien, donc void, puisque son rôle c'est simplement de décrémenter
    public function decrement(int $id) : void 
    {
        // Récupérer le panier
        $cart = $this->getCart();

        // Si le produit n'existe pas dans le panier on ne fait rien
        if ( ! \array_key_exists($id, $cart) ) 
        {
            return; // On arrête la execution du script
        }

        // Dans le cas contraire je vérifie si la quantité du produit est à 1 dans le panier, parce que si c'est à 1 je ne peux plus décrémenter car c'est 1 qui est la valeur minimale qui doit être affectée à chaque produit dans le panier.
            // Si la quantité du produit est à 1
                // Retirer le produit du panier
        if ($cart[$id] == 1 ) 
        {
            $this->remove($id);
            return;
        }

        // Dans le cas contarire 
            //  Décrémenter le produit de 1
        $cart[$id]--;

        // Mettre à jour le panier
        $this->setCart($cart);
    }

    public function remove( int $id) : void // Cette méthode ne retourne rien elle fait juste un traitement donc c'est une procédure
    {
        // Récupérer le panier
        $cart = $this->getCart();

        // Ensuite dans ce panier je demande à récupérer la valeur associée à la clé id
            // Et cette valeur je vais la supprimer du tableau ainsi que sa clé
        unset($cart[$id]); 

        // Puis je mest à jour le panier
        $this->setCart($cart);
    }

    public function emptyCart()
    {
        $this->requestStack->getSession()->set('cart', []);
    }
}