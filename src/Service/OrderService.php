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
            private EntityManagerInterface $em,
            private Security $security,
            private CartService $cartService
        )
        {
            
        }

        public function persist(DateTimeImmutable $pickupDate, \DateTimeImmutable $pickupTime) : void
        {
            $order = new Order();

            /**
             * @var User
             */
            $user = $this->security->getUser();

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
        }
    }