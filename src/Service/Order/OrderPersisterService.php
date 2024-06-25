<?php
namespace App\Service\Order;

use App\Entity\Order;
use DateTimeImmutable;
use App\Entity\Address;
use App\Entity\Carrier;
use App\Entity\OrderItem;
use App\Repository\ProductRepository;
use App\Service\Cart\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

    class OrderPersisterService
    {

        private Order $order;

        public function __construct(
            private CartService $cartService,
            private Security $security,
            private EntityManagerInterface $em,
            ProductRepository $productRepository
        )
        {
        }
        
        public function persist(DateTimeImmutable $pickupDate, string $pickupTime) : order
        {
            $order = new Order();

            /**
             * @var User
             */
            $user = $this->security->getUser();

            // dd($pickupDate, $pickupTime);

            $order
                ->setUser($user)
                ->setUserEmail($user->getEmail())
                ->setPickupFirstName($user->getFirstName())
                ->setPickupLastName($user->getLastName())
                ->setTotalAmount($this->cartService->getCartTotalAmount())
                ->setPickupDate($pickupDate)
                ->setPickupTime($pickupTime)
                ->setStatus(Order::STATUS_PENDING)
                ->setOrderedAt(new DateTimeImmutable())
                ->setUpdatedAt(new DateTimeImmutable())

            ;

            $this->em->persist($order);

            foreach ($this->cartService->getCartItems() as $cartItem) 
            {
                $orderDetail= new OrderDetail();

                $orderDetail
                        ->setTheOrder($order)
                        ->setProduct($cartItem->product)
                        ->setProductName($cartItem->product->getName())
                        ->setProductPrice($cartItem->product->getPrice())
                        ->setProductQuantity($cartItem->quantity)
                        ->setTotalAmount($cartItem->getAmount())
               ;

               $this->em->persist($orderDetail);
            }
           
            $this->em->flush();   

            return $order; // Return the order entity
        }

        public function getOrder(): Order
        {
            return $this->order;
        }

        public function setOrder(Order $order) : void
        {
            $this->order = $order;
        }
    }