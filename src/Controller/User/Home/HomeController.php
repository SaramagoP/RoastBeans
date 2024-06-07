<?php

namespace App\Controller\User\Home;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
class HomeController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/home', name: 'user_home_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $orders = $orderRepository->findBy(['user' => $user]);

        return $this->render('pages/user/home/index.html.twig', [
            'orders' => $orders
        ]);
    }
}