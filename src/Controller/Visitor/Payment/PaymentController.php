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


#[Route('/checkout')]
class PaymentController extends AbstractController
{

    public function __construct(
        private OrderPersisterService $orderPersisterService,
        private OrderRepository $orderRepository,
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $em,
        private CartService $cartService,
        private StripeService $stripeService
    )
    {
    } 

    #[Route('/{id<\d+>}', name: 'app_checkout', methods:['GET'])]
    public function index($id): Response
    { 

        $order = $this->orderRepository->find($id);

        if ( ! $order ) 
        {
            return $this->redirectToRoute("visitor.cart.index");
        }

        $items = $order->getOrderDetails()->toArray();
        $data = [];

        foreach ($items as $item) 
        {
            $data[] = [
                'price_data' => [
                    'currency' => 'EUR',
                    'unit_amount' => $item->getProduct()->getPrice() * 100,
                    'product_data' => [
                        'name' => $item->getProduct()->getName()
                    ]
                ],
                'quantity' => $item->getProductQuantity() 
            ];
        }

        $stripeSecretKey = $this->stripeService->getStripeApiSecret();

        \Stripe\Stripe::setApiKey($stripeSecretKey);

        /**
         * @var \App\Entity\User
         */
        $user = $this->getUser();

        $checkout_session = \Stripe\Checkout\Session::create([
            'customer_email' => $user->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [
                $data
            ],
            'mode' => 'payment',
            'success_url' => "https://localhost:8000/checkout/{$order->getId()}/success",
            'cancel_url'  => "https://localhost:8000/checkout/{$order->getId()}/cancel",

            // 'success_url' => $this->urlGenerator->generate('visitor.payment.success', [
            //     "id" => $order->getId()
            // ]),
            // 'cancel_url'  => $this->urlGenerator->generate('visitor.payment.cancel', [
            //     "id" => $order->getId()
            // ]),
        ]);

        $order->setStatus($order::STATUS_PAYMENT_PENDING);

        $this->em->persist($order);
        $this->em->flush();

        return new RedirectResponse($checkout_session->url);
    }

    #[Route('/{id<\d+>}/success', name: 'app_checkout_success', methods:['GET'])]
    public function success(Order $order): Response
    {

        $order->setStatus($order::STATUS_PAYMENT_SUCCESSFULLY);

        $this->em->persist($order);
        $this->em->flush();

        // Vider le panier
        $this->cartService->emptyCart();

        return $this->render("pages/visitor/payment/success.html.twig", [
            "order" => $order
        ]);
    }
    
    #[Route('/{id<\d+>}/cancel', name: 'app_checkout_cancel', methods:['GET'])]
    public function cancel(Order $order): Response
    {

        $order->setStatus($order::STATUS_PAYMENT_CANCEL);

        $this->em->persist($order);
        $this->em->flush();

        return $this->render("pages/visitor/payment/cancel.html.twig");
    }
}
