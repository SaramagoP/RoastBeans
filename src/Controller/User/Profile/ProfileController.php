<?php

namespace App\Controller\User\Profile;


use DateTimeImmutable;
use App\Form\EditProfilFormType;
use App\Form\EditPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class ProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    )
    {
        // Le constructeur de la classe.
        // Il est utilisé pour initialiser des propriétés ou des dépendances lorsqu'une instance de cette classe est créée.

        // Le paramètre $em est de type EntityManagerInterface.
        // Il s'agit probablement d'une dépendance injectée dans la classe.

        // La propriété $em est initialisée avec la valeur du paramètre $em.
    }

    #[Route('/profile', name: 'user_profile_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/user/profile/index.html.twig');
    }

    #[Route('/profile/edit', name: 'user_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(Request $request): Response
    {
        // Récupère l'utilisateur actuellement connecté (l'administrateur)
        $user = $this->getUser();

        // Crée un formulaire en utilisant la classe EditProfilFormType
        // Le formulaire est lié à l'entité utilisateur ($admin) et configuré pour utiliser la méthode HTTP PUT
        $form = $this->createForm(EditProfilFormType::class, $user, [
            "method" => "POST"
        ]);

        // Traite la requête HTTP actuelle et met à jour le formulaire avec les données soumises
        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et si les données sont valides
        if ($form->isSubmitted() && $form->isValid())
        {
            $user->setUpdatedAt(new DateTimeImmutable());
            
            // Persiste l'entité utilisateur dans la base de données
            $this->em->persist($user);

            // Enregistre les modifications dans la base de données
            $this->em->flush();

            // Ajoute un message flash pour informer l'utilisateur que la modification du profil a réussi
            $this->addFlash('success', "Le profil de {$user->getFirstName()} {$user->getLastName()} a été modifié avec succès.");

            // Redirige l'utilisateur vers la page d'index du profil useristrateur
            return $this->redirectToRoute('user_profile_index');
        }

        // Si le formulaire n'est pas soumis ou n'est pas valide, rend la vue Twig edit_profile.html.twig
        // avec le formulaire affiché pour l'utilisateur
        return $this->render('pages/user/profile/edit_profile.html.twig', [
            "form" => $form->createView()
        ]);
    }


    #[Route('/profile/edit-password', name: 'user_profile_edit_password', methods: ['GET', 'POST'])]
    public function editPassword(Request $request): Response
    {
        
        $user = $this->getUser();

        $form = $this->createForm(EditPasswordFormType::class, null, [
            "method" => "POST"
        ]);

        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et si les données sont valides
        if ($form->isSubmitted() && $form->isValid())
        {

            $plainPassword = $form->getData()['password'];

            $passwordHashed = $this->hasher->hashPassword($user, $plainPassword);

            $user->setPassword($passwordHashed);

            $user->setUpdatedAt(new DateTimeImmutable());

            // Persiste l'entité utilisateur dans la base de données
            $this->em->persist($user);

            // Enregistre les modifications dans la base de données
            $this->em->flush();

            // Ajoute un message flash pour informer l'utilisateur que la modification du profil a réussi
            $this->addFlash('success', "Le mot de passe a été modifié avec succès.");

            // Redirige l'utilisateur vers la page d'index du profil useristrateur
            return $this->redirectToRoute('user_profile_index');
        }

        return $this->render('pages/user/profile/edit_password.html.twig', [
            "form" => $form->createView()
        ]);
    }

    
    // Annotation de route pour définir l'URL, le nom de la route et la méthode HTTP autorisée
    #[Route('/profile/delete', name: 'user_profile_delete', methods: ['DELETE'])]
    public function deleteProfile(Request $request): Response
    {
        // Vérifie si le jeton CSRF est valide
        if ($this->isCsrfTokenValid('delete_profile', $request->request->get('_csrf_token')))
        {
            // Récupère l'utilisateur actuellement connecté
            $user = $this->getUser();

            // Invalide le jeton de sécurité actuel pour déconnecter l'utilisateur
            $this->container->get('security.token_storage')->setToken(null);

            // Supprime l'entité utilisateur de la base de données
            $this->em->remove($user);
            
            // Applique les changements à la base de données
            $this->em->flush();
            
            // Ajoute un message flash de succès pour informer l'utilisateur que le profil a été supprimé
            $this->addFlash('success', "Le profil de {$user->getFirstName()} {$user->getLastName()} a été supprimé avec succès.");

            // Redirige l'utilisateur vers la page d'index des profils utilisateur
            return $this->redirectToRoute('user_profile_index');
        }

        // Si le jeton CSRF est invalide, vous pourriez vouloir ajouter une redirection ou un message d'erreur ici
        // Note: Une réponse par défaut est nécessaire pour éviter des erreurs de retour manquant.
        $this->addFlash('error', 'Invalid CSRF token.');
        return $this->redirectToRoute('user_profile_index');
    }
}
