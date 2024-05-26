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
    #[Route('/admin/contact/list', name: 'admin_contact_index', methods: ['GET'])]
    public function index(ContactRepository $contactRepository): Response
    {
        $contacts = $contactRepository->findAll();


        return $this->render('pages/admin/contact/index.html.twig', [
            "contacts" =>$contacts
        ]);
    }


    #[Route('/admin/contact/{id<\d+>}/delete', name: 'admin_contact_delete', methods: ['POST', 'DELETE'])]
    public function delete(Contact $contact, Request $request, EntityManagerInterface $em): Response
    {
        if ( $this->isCsrfTokenValid('delete_contact_'.$contact->getId(), $request->request->get('_csrf_token')) )
        {
            $this->addFlash('success', "Le contact a été supprimée avec succès.");
            
            $em->remove($contact);
            
            $em->flush();
            

            return $this->redirectToRoute('admin_contact_index');
        }
    }
}
