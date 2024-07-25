<?php

namespace App\Controller\Visitor\Order;


use App\Entity\Order;
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
    // Constructeur pour injecter les services nécessaires.
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

        $order = new Order();

        //  2- Vérifier si les produits sont toujours dans le panier
        if ( count ($this->cartService->getCartItems()) <= 0 )
        {
            $this->addFlash('warning', "Un problème est survenu, veillez rajouter les produits au panier");

            return $this->redirectToRoute('visitor_cart_index');
        }

        // 3- Créer le formulaire de commande
        $form = $this->createForm(OrderFormType::class, [
            "user" => $user
        ]);

        // 6- Associer au formulaire, les données de la requête
        $form->handleRequest($request);

        // 7- Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid())
        {
            // Récupérer les données du formulaire
            $data = $request->request->all()['order_form'];

            // Extraire la date et l'heure de retrait
            $pickupDate = $data['pickup_date'];
            $pickupTime = $data['pickup_time'];

            // Convertir la date en objet DateTimeImmutable
            $pickupDate = new DateTimeImmutable($pickupDate);

            // Persister la commande en utilisant le service OrderService
            $order = $this->orderService->persist($pickupDate, $pickupTime);

            // Rediriger vers la page de paiement
            return $this->redirectToRoute('app_checkout', [
                'id' => $order->getId()
            ]);
        }
        
        // 4- Passer le formulaire à la vue
        return $this->render('pages/visitor/order/index.html.twig', [
            "order" => $order,
            "form" =>$form->createView(),
            "cartItems" =>$this->cartService->getCartItems()
        ]);
    }
}
