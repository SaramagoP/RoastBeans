<?php

namespace App\Controller\Visitor\Contact;

use DateTimeImmutable;
use App\Entity\Contact;
use App\Form\ContactFormType;
use App\Repository\SettingsRepository;
use App\Service\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'visitor_contact_create', methods:['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, SendEmailService $SendEmailService, SettingsRepository $settingsRepository): Response
    {
        $contact = new Contact();
        $settings = $settingsRepository->findAll();

        $setting = $settings[0];

        $form = $this->createForm(ContactFormType::class, $contact);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() )
        {
            $contact->setMessage(htmlspecialchars($contact->getMessage(), ENT_QUOTES, 'UTF-8'));

            if ($this->getUser()) 
            {
                $contact->setUser($this->getUser());
            }
            else
            {
                $contact->setUser(null);
            }

            $contact->setCreatedAt(new DateTimeImmutable());

            $em->persist($contact);

            $em->flush(); 

            // Envoi de l'email
            $SendEmailService->send([
                "sender_email" => "roastbeans@gmail.com",
                "sender_name" => "Pierre Dubois",
                "recipient_email" => "roastbeans@gmail.com",
                "subject" => "Un message reçu sur votre site",
                "html_template" => "emails/contact.html.twig",
                "context" => [
                    "contact_first_name"    => $contact->getFirstName(),
                    "contact_last_name"     => $contact->getLastName(),
                    "contact_email"         => $contact->getEmail(),
                    "contact_phone"         => $contact->getPhone(),
                    "contact_message"       => $contact->getMessage(),
                    "user"                  => $contact->getUser()
                ]
            ]);

            $this->addFlash("success", "Votre message a bien été envoyé. Nous vous contacterons dans les plus brefs délais.");

            return $this->redirectToRoute('visitor_contact_create');
        }

        return $this->render('pages/visitor/contact/create.html.twig', [
            "form" => $form->createView(),
            "setting" => $setting
        ]);
    }
}

