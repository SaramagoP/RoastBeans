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
    // Constructeur de la classe UserController, qui injecte EntityManagerInterface et UserRepository pour interagir avec la base de données.
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository
    )
    {
        // Injection des dépendances EntityManagerInterface et UserRepository
    }

    #[Route('/user/list', name: 'admin_user_index', methods: ['GET'])]
    // on utilise la methode public, qu'est accessible dehors de la classe et renvoie un objet Response. Il prend un argument $userRepository de type UserRepository. Le UserRepository est automatiquement injecté dans la méthode par le système d’injection de dépendances de Symfony.
    public function index(UserRepository $userRepository): Response
    {
        // Affiche la liste des utilisateurs
        return $this->render('pages/admin/user/index.html.twig', [ // render : Rend le template index.html.twig avec tous les utilisateurs récupérés depuis la base de données.
            "users" => $userRepository->findAll()//Cette méthode récupère tous les utilisateurs enrgistré on base de données
        ]);
    }

// L'EntityManager est la classe centrale de Doctrine ORM pour gérer les entités (objets qui représentent des lignes de table dans la base de données). Il fournit les méthodes persist et flush.

// Persist : La méthode persist informe l'EntityManager qu'une entité (un objet) doit être suivie et qu'elle doit être insérée dans la base de données à la prochaine opération de flush. Elle ne modifie pas immédiatement la base de données.

// Flush : La méthode flush envoie toutes les modifications en attente (persist, remove, update) à la base de données. C'est à ce moment-là que les insertions, mises à jour et suppressions sont réellement exécutées dans la base de données.

    #[Route('/user/create', name: 'admin_user_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        // Crée un nouvel utilisateur
        $user = new User();

        // Crée un formulaire pour éditer les rôles de l'utilisateur
        $form = $this->createForm(EditRolesFormType::class, $user);

        // Gère la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // Met à jour les horodatages de création et de mise à jour
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setUpdatedAt(new DateTimeImmutable());

            // Persiste l'utilisateur dans la base de données
            $this->em->persist($user);
            $this->em->flush();

            // Ajoute un message flash pour indiquer le succès de l'opération
            $this->addFlash('success', "L'utilisateur a été ajouté avec succès.");

            // Redirige vers la liste des utilisateurs
            return $this->redirectToRoute('admin_user_index');
        }

        // Affiche le formulaire de création d'utilisateur
        return $this->render('pages/admin/user/create.html.twig', [
            "form" => $form->createView()
        ]);
    }

    // \d+ est une expression régulière qui signifie "un ou plusieurs chiffres supérieurs à zéro".
    #[Route('/user/{id<\d+>}/edit-roles', name: 'admin_user_edit_roles', methods: ['GET', 'POST'])]
    public function editRoles(User $user, Request $request): Response
    {
        // Crée un formulaire pour éditer les rôles de l'utilisateur
        $form = $this->createForm(EditRolesFormType::class, $user, [
            "method" => "POST"
        ]);

        // Gère la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // Met à jour les horodatages de création et de mise à jour
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setUpdatedAt(new DateTimeImmutable());

            // Persiste l'utilisateur dans la base de données
            $this->em->persist($user);
            $this->em->flush();

            // Ajoute un message flash pour indiquer le succès de l'opération
            $this->addFlash('success', "Les rôles de {$user->getFirstName()} {$user->getLastName()} ont été modifiés avec succès.");

            // Redirige vers la liste des utilisateurs
            return $this->redirectToRoute('admin_user_index');
        }

        // Affiche le formulaire d'édition des rôles de l'utilisateur
        return $this->render('pages/admin/user/edit_roles.html.twig', [
            "form" => $form->createView(),
            "user" => $user
        ]);
    }

    #[Route('/user/{id<\d+>}/delete-roles', name: 'admin_user_delete_roles', methods: ['DELETE'])]
    public function delete(User $user, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_csrf_token')))
        {
            // Ajoute un message flash pour indiquer le succès de la suppression
            $this->addFlash('success', "Le compte de {$user->getFirstName()} {$user->getLastName()} a été supprimé avec succès.");

            // Supprime l'utilisateur de la base de données
            $this->em->remove($user);
            $this->em->flush();

            // Redirige vers la liste des utilisateurs
            return $this->redirectToRoute('admin_user_index');
        }
    }
}