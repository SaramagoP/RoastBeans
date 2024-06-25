<?php

namespace App\Controller\Admin\Order;


use App\Entity\Order;
use DateTimeImmutable;
use Symfony\Component\Mime\Email;
use App\Repository\OrderRepository;
use Symfony\Component\Mime\Address;
use App\Form\EditOrderStatusFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class OrderController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EntityManagerInterface $em,
        private MailerInterface $mailer
    )
    {    
    }


    #[Route('/order/list', name: 'admin_order_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/order/index.html.twig', [
            "orders" =>$this->orderRepository->findAll()
        ]);
    }


    #[Route('/order/{id<\d+>}/edit/status', name: 'admin_order_status_edit', methods: ['GET', 'POST'])]
    public function editStatus(Order $order, Request $request): Response
    {
        $form = $this->createForm(EditOrderStatusFormType::class, $order);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $order->setUpdatedAt(new DateTimeImmutable());

            $this->em->persist($order);

            $this->em->flush();

            // Envoyer email de confirmation
            $this->sendConfirmationEmail($order);

            $this->addFlash('success', 'Le status de la commande a été modifié.');

            return $this->redirectToRoute('admin_order_index');
        }

        return $this->render('pages/admin/order/edit_status.html.twig', [
           "form" => $form->createView()
        ]);
    }


    #[Route('/order/{id<\d+>}/delete', name: 'admin_order_delete', methods: ['POST', 'DELETE'])]
    public function delete(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        // Vérification de la validité du token CSRF pour des raisons de sécurité
        if ($this->isCsrfTokenValid('delete_order_' . $order->getId(), $request->request->get('_csrf_token'))) {
            // Ajout d'un message flash de succès
            $this->addFlash('success', 'La commande a été supprimé avec succès.');
            
            // Demander au manager des entités de préparer la suppression du order en base de données
            $em->remove($order);
            // Validation des modifications dans la base de données
            $em->flush();

            // Redirection vers la page d'index des orders
            return $this->redirectToRoute('admin_order_index');
        }

        // Ajout d'un message flash d'erreur si le token CSRF n'est pas valide
        $this->addFlash('error', 'Token CSRF invalide, suppression échouée.');

        // Redirection vers la page d'index des orders si le token CSRF n'est pas valide
        return $this->redirectToRoute('admin_order_index');
    }

    private function sendConfirmationEmail(Order $order): void
    {
        $email = (new Email())
           ->from(new Address('roastbeans@gmail.com', 'Pierre Dubois'))
            ->to($order->getUserEmail())
            ->subject('Confirmation de commande validé')
            ->html($this->renderView('emails/order_confirmation.html.twig', [
                'order' => $order
            ]));

        $this->mailer->send($email);
    }
}
 
