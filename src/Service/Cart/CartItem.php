<?php

namespace App\Service\Cart;

use App\Entity\Product;

    class CartItem
    {
        public function __construct(
            public Product $product,
            public int $quantity
        )
        {  
            // Le constructeur de la classe CartItem prend deux paramètres :
            // $product : Un objet de type Product qui représente le produit associé à cet élément du panier.
            // $quantity : Un entier qui représente la quantité de ce produit dans le panier.

            // Ces paramètres sont initialisés directement dans les propriétés publiques $product et $quantity grâce à la syntaxe raccourcie introduite dans PHP 7.4.
        }

        public function getAmount() :float
        {
            // Méthode getAmount() : Calcule et retourne le montant total pour cet élément du panier.
            // Le montant est calculé en multipliant le prix du produit par la quantité de ce produit dans le panier.
            return $this->product->getPrice() * $this->quantity;
        }
    }