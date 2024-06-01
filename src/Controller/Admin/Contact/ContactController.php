<?php

namespace App\Controller\Admin\Contact;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    // Définition de la route pour afficher la liste des contacts
    #[Route('/admin/contact/list', name: 'admin_contact_index', methods: ['GET'])]
     // Définition de la méthode "index" de ce contrôleur
   // Cette méthode prend en paramètre un objet de type ContactRepository et retourne une réponse
    public function index(ContactRepository $contactRepository): Response
    {
        // Récupérer tous les contacts depuis le repository
        $contacts = $contactRepository->findAll();

        // Retourner la vue pour afficher la liste des contacts
        return $this->render('pages/admin/contact/index.html.twig', [
            "contacts" =>$contacts
        ]);
    }


    #[Route('/admin/contact/{id<\d+>}/delete', name: 'admin_contact_delete', methods: ['POST', 'DELETE'])]
    public function delete(Contact $contact, Request $request, EntityManagerInterface $em): Response
    {
        // Vérification de la validité du token CSRF pour des raisons de sécurité
        if ($this->isCsrfTokenValid('delete_contact_' . $contact->getId(), $request->request->get('_csrf_token'))) {
            // Ajout d'un message flash de succès
            $this->addFlash('success', 'Le contact a été supprimé avec succès.');
            
            // Demander au manager des entités de préparer la suppression du contact en base de données
            $em->remove($contact);
            // Validation des modifications dans la base de données
            $em->flush();

            // Redirection vers la page d'index des contacts
            return $this->redirectToRoute('admin_contact_index');
        }

        // Ajout d'un message flash d'erreur si le token CSRF n'est pas valide
        $this->addFlash('error', 'Token CSRF invalide, suppression échouée.');

        // Redirection vers la page d'index des contacts si le token CSRF n'est pas valide
        return $this->redirectToRoute('admin_contact_index');
    }
}
