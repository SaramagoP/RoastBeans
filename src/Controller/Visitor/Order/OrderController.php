<?php

namespace App\Controller\Visitor\Order;


use DateTimeImmutable;
use App\Form\OrderFormType;
use App\Service\OrderService;
use App\Service\Cart\CartService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/order')]
class OrderController extends AbstractController
{
    public function __construct( 
        private CartService $cartService,
        private OrderService $orderService
    )
    {
    }

    #[Route('/recap', name: 'app_order_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        /**
         * 1- Récupérer l'utilisateur actuellement connecté
         * 
         * @var User
         */
        $user = $this->getUser();

        //  2- Vérifier si les produits sont toujours dans le panier
        if ( count ($this->cartService->getCartItems()) <= 0 )
        {
            $this->addFlash('warning', "Un problème est survenu, veillez rajouter les produits au panier");

            return $this->redirectToRoute('user_cart_index');
        }

        // 3- Créer le formulaire de commande
        $form = $this->createForm(OrderFormType::class, null, [
            "user" => $user
        ]);

        // 6- Associer au formulaire, les données de la requête
        $form->handleRequest($request);

        // 7- Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid())
        {
          
            $pickupTime = $form->get('pickup_time')->getData();
            $pickupDate = $form->get('pickup_date')->getData();

           // Convertir les objets DateTime en DateTimeImmutable
            $pickupTime = DateTimeImmutable::createFromMutable($pickupTime);
            $pickupDate = DateTimeImmutable::createFromMutable($pickupDate);

            $this->orderService->persist($pickupDate, $pickupTime);

            return $this->redirectToRoute('app_payment_index');
        }
        
        // 4- Passer le formulaire à la vue
        return $this->render('pages/visitor/order/index.html.twig', [
            "form" =>$form->createView(),
            "cartItems" =>$this->cartService->getCartItems()
        ]);
    }
}
