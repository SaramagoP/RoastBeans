<?php

namespace App\Controller\Visitor\Payment;


use App\Entity\Order;
use App\Service\StripeService;
use App\Service\Cart\CartService;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Order\OrderPersisterService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/payment')]
class PaymentController extends AbstractController
{

    public function __construct(
        private OrderPersisterService $orderPersisterService, // Service pour persister les commandes
        private OrderRepository $orderRepository, // Repository pour accéder aux commandes en base de données
        private UrlGeneratorInterface $urlGenerator, // Pour générer des URL
        private EntityManagerInterface $em, // Pour gérer la persistance des entités
        private CartService $cartService, // Pour gérer le panier de l'utilisateur
        private StripeService $stripeService // Pour intégrer avec Stripe
    )
    {
    } 

    #[Route('/{id<\d+>}', name: 'app_checkout', methods:['GET'])]
    public function index($id): Response
    { 
        // Récupération de la commande en fonction de l'ID fourni dans l'URL
        $order = $this->orderRepository->find($id);

        // Si la commande n'existe pas, redirige vers la page du panier
        if ( ! $order ) 
        {
            return $this->redirectToRoute("visitor.cart.index");
        }

        // Préparation des données pour Stripe Checkout
        $items = $order->getOrderDetails()->toArray();
        $data = [];

        foreach ($items as $item) 
        {
            $data[] = [
                'price_data' => [
                    'currency' => 'EUR',
                    'unit_amount' => $item->getProduct()->getPrice() * 100, // Montant en centimes
                    'product_data' => [
                        'name' => $item->getProduct()->getName()
                    ]
                ],
                'quantity' => $item->getProductQuantity() 
            ];
        }

        // Configuration de la clé API Stripe
        $stripeSecretKey = $this->stripeService->getStripeApiSecret();

        \Stripe\Stripe::setApiKey($stripeSecretKey);

        /**
         * @var \App\Entity\User
         */
        $user = $this->getUser();

        // Création d'une session de paiement Stripe
        $checkout_session = \Stripe\Checkout\Session::create([
            'customer_email' => $user->getEmail(),
            'payment_method_types' => ['card'], // Méthodes de paiement acceptées
            'line_items' => [
                $data // Les articles de la commande
            ],
            'mode' => 'payment',
            'success_url' => "https://localhost:8000/payment/{$order->getId()}/success", // URL de redirection en cas de succès
            'cancel_url'  => "https://localhost:8000/payment/{$order->getId()}/cancel", // URL de redirection en cas d'annulation
        ]);

        // Mise à jour du statut de la commande à "en attente de paiement"
        $order->setStatus($order::STATUS_PAYMENT_PENDING);

        $this->em->persist($order); // Marque la commande pour persistance
        $this->em->flush(); // Enregistre les modifications en base de données

        // Redirection vers l'URL de Stripe Checkout
        return new RedirectResponse($checkout_session->url);
    }

    #[Route('/{id<\d+>}/success', name: 'app_checkout_success', methods:['GET'])]
    public function success(Order $order): Response
    {
        // Mise à jour du statut de la commande à "paiement réussi"
        $order->setStatus($order::STATUS_PAYMENT_SUCCESSFULLY);

        $this->em->persist($order);
        $this->em->flush();

        // Vide le panier de l'utilisateur après le paiement réussi
        $this->cartService->emptyCart();

        return $this->render("pages/visitor/payment/success.html.twig", [
            "order" => $order
        ]);
    }
    
    #[Route('/{id<\d+>}/cancel', name: 'app_checkout_cancel', methods:['GET'])]
    public function cancel(Order $order): Response
    {
        // Mise à jour du statut de la commande à "annulation du paiement"
        $order->setStatus($order::STATUS_PAYMENT_CANCEL);

        $this->em->persist($order);
        $this->em->flush();

        return $this->render("pages/visitor/payment/cancel.html.twig");
    }
}
