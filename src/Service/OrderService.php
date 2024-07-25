<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Order;
use DateTimeImmutable;
use App\Entity\OrderDetail;
use App\Service\Cart\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

    class OrderService
    {
        public function __construct(
            private EntityManagerInterface $em, // Pour gérer la persistance des entités dans la base de données
            private Security $security, // Pour obtenir l'utilisateur actuellement connecté
            private CartService $cartService // Pour obtenir les détails du panier de l'utilisateur
        )
        {
            
        }

    /**
     * Crée une nouvelle commande, persiste les détails de la commande et des produits dans la base de données.
     *
     * @param DateTimeImmutable $pickupDate La date de collecte de la commande
     * @param string $pickupTime L'heure de collecte de la commande
     * @return Order L'objet Order créé et persisté
     */

        public function persist(DateTimeImmutable $pickupDate, string $pickupTime) : order
        {
            $order = new Order(); // Création d'une nouvelle instance d'Order

            /**
             * @var User
             */
            $user = $this->security->getUser(); // Récupère l'utilisateur actuellement connecté

            $order
                ->setUser($user)
                ->setUserEmail($user->getEmail())
                ->setPickupFirstName($user->getFirstName())
                ->setPickupLastName($user->getLastName())
                ->setTotalAmount($this->cartService->getCartTotalAmount()) // Total du panier
                ->setPickupDate($pickupDate)
                ->setPickupTime($pickupTime)
                ->setStatus(Order::STATUS_PAYMENT_PENDING) // Status initial de la commande
                ->setOrderedAt(new DateTimeImmutable())
                ->setUpdatedAt(new DateTimeImmutable())

            ;

            $this->em->persist($order); // Marque la commande pour la persistance en base de données

            // Pour chaque élément du panier, créer un détail de commande et le persister
            foreach ($this->cartService->getCartItems() as $cartItem) 
            {
                $orderDetail= new OrderDetail();

                $orderDetail
                        ->setTheOrder($order)
                        ->setProduct($cartItem->product) // Produit associé
                        ->setProductName($cartItem->product->getName())
                        ->setProductPrice($cartItem->product->getPrice())
                        ->setProductQuantity($cartItem->quantity) // Quantité du produit
                        ->setTotalAmount($cartItem->getAmount()) // Montant total pour cet article
               ;

               $this->em->persist($orderDetail); // Marque le détail de commande pour la persistance en base de données
            }
           
            $this->em->flush();   

            return $order; // Retourne la commande créée 
        }
    
    /**
     * Met à jour le statut d'une commande existante.
     *
     * @param Order $order La commande à mettre à jour
     * @param string $status Le nouveau statut de la commande
     */

        public function updateOrderStatus(Order $order, string $status): void
        {
            $order->setStatus($status); // Met à jour le statut de la commande
            $this->em->flush(); // Persiste la mise à jour en base de données
        }
    }