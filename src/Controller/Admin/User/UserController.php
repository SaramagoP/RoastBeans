<?php

namespace App\Controller\Admin\User;

use App\Entity\User;
use DateTimeImmutable;
use App\Form\EditRolesFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class UserController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository
    )
    {
        
    }
    #[Route('/user/list', name: 'admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('pages/admin/user/index.html.twig', [
            "users" => $userRepository->findAll()
        ]);
    }


    #[Route('/user/create', name: 'admin_user_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(EditRolesFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setUpdatedAt(new DateTimeImmutable());

            $this->em->persist($user);

            $this->em->flush();

            $this->addFlash('success', "L'user a été ajoutée avec succès.");

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('pages/admin/user/create.html.twig', [
            "form" => $form->createView()
        ]);
    }


    #[Route('/user/{id<\d+>}/edit-roles', name: 'admin_user_edit_roles', methods: ['GET', 'PUT'])]
    public function editRoles(User $user, Request $request): Response
    {
        $form = $this->createForm(EditRolesFormType::class, $user, [
            "method" => "PUT"           
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setUpdatedAt(new DateTimeImmutable());

            $this->em->persist($user);

            $this->em->flush();

            $this->addFlash('success', "Les rôles de {$user->getFirstName()} {$user->getLastName()} ont été modifié avec succès.");

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('pages/admin/user/edit_roles.html.twig', [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }


    #[Route('/user/{id<\d+>}/delete-roles', name: 'admin_user_delete_roles', methods: ['DELETE'])]
    public function delete(User $user, Request $request): Response
    {
        if ( $this->isCsrfTokenValid('delete_user_'.$user->getId(), $request->request->get('_csrf_token')) )
        {
            $this->addFlash('success', "Le compte de {$user->getFirstName()} {$user->getLastName()} a été supprimée avec succès.");
            
            $this->em->remove($user);
            
            $this->em->flush();
            

            return $this->redirectToRoute('admin_user_index');
        }
    }
}
