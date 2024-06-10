<?php

namespace App\Controller\User\Order;

use App\Entity\Order;
use DateTimeImmutable;
use App\Form\EditOrderFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/user')]
class OrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {  
    }

    
    #[Route('/order/list', name: 'user_order_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/user/order/index.html.twig');
    }


    #[Route('/order/{id<\d+>}/edit', name: 'user_order_edit', methods: ['GET', 'POST'])]
    public function edit(Order $order, Request $request): Response
    {
        $form = $this->createForm(EditOrderFormType::class, $order, [
            'method' => "POST",
        ]);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            $order->setUser($this->getUser());

            $order->setupdatedAt(new DateTimeImmutable());

            $this->em->persist($order);
            
            $this->em->flush();

            $this->addFlash('success', "La commande numéro {$order->getId()} a été modifié.");

            return $this->redirectToRoute("user_order_index");
        }

        return $this->render('pages/user/order/edit.html.twig', [
            "form" => $form->createView()
        ]);
    }


    
    #[Route('/order/{id<\d+>}/delete', name: 'user_order_delete', methods: ['POST', 'DELETE'])]
    public function delete(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        // Vérification de la validité du token CSRF pour des raisons de sécurité
        if ($this->isCsrfTokenValid('delete_order_' . $order->getId(), $request->request->get('_csrf_token'))) {
            // Ajout d'un message flash de succès
            $this->addFlash('success', "La commande numéro {$order->getId()} a été supprimé avec succès.");
            
            // Demander au manager des entités de préparer la suppression du order en base de données
            $em->remove($order);
            // Validation des modifications dans la base de données
            $em->flush();

            // Redirection vers la page d'index des orders
            return $this->redirectToRoute('user_order_index');
        }

        // Ajout d'un message flash d'erreur si le token CSRF n'est pas valide
        $this->addFlash('error', 'Token CSRF invalide, suppression échouée.');

        // Redirection vers la page d'index des orders si le token CSRF n'est pas valide
        return $this->redirectToRoute('user_order_index');
    }
}

