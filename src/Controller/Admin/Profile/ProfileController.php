<?php

namespace App\Controller\Admin\Profile;


use App\Entity\User;
use App\Form\EditProfilFormType;
use App\Form\EditPasswordFormType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin')]
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

    #[Route('/profile', name: 'admin_profile_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/profile/index.html.twig');
    }


    #[Route('/profile/edit', name: 'admin_profile_edit', methods: ['GET', 'PUT'])]
    public function editProfile(Request $request): Response
    {
        // Récupère l'utilisateur actuellement connecté (l'administrateur)
        /** @var User $admin */
        $admin = $this->getUser();

        
        // Crée un formulaire en utilisant la classe EditProfilFormType
        // Le formulaire est lié à l'entité utilisateur ($admin) et configuré pour utiliser la méthode HTTP PUT
        $form = $this->createForm(EditProfilFormType::class, $admin, [
            "method" => "PUT"
        ]);
        
        // Traite la requête HTTP actuelle et met à jour le formulaire avec les données soumises
        $form->handleRequest($request);
        
        // Vérifie si le formulaire a été soumis et si les données sont valides
        if ($form->isSubmitted() && $form->isValid())
        {
            // Echappe les données pour le message flash
            $firstName = htmlspecialchars($admin->getFirstName(), ENT_QUOTES, 'UTF-8');
            $lastName = htmlspecialchars($admin->getLastName(), ENT_QUOTES, 'UTF-8');
            
            $admin->setUpdatedAt(new DateTimeImmutable());

            // Persiste l'entité utilisateur dans la base de données
            $this->em->persist($admin);

            // Enregistre les modifications dans la base de données
            $this->em->flush();

            // Ajoute un message flash pour informer l'utilisateur que la modification du profil a réussi
            $this->addFlash('success', "Le profil de {$admin->getFirstName()} {$admin->getLastName()} a été modifié avec succès.");

            // Redirige l'utilisateur vers la page d'index du profil administrateur
            return $this->redirectToRoute('admin_profile_index');
        }

        // Si le formulaire n'est pas soumis ou n'est pas valide, rend la vue Twig edit_profile.html.twig
        // avec le formulaire affiché pour l'utilisateur
        return $this->render('pages/admin/profile/edit_profile.html.twig', [
            "form" => $form->createView()
        ]);
    }


    #[Route('/profile/edit-password', name: 'admin_profile_edit_password', methods: ['GET', 'PUT'])]
    public function editPassword(Request $request): Response
    {
        /** @var User */
        $admin = $this->getUser();

        $form = $this->createForm(EditPasswordFormType::class, null, [
            "method" => "PUT"
        ]);

        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et si les données sont valides
        if ($form->isSubmitted() && $form->isValid())
        {
            // Récupère le mot de passe en clair à partir des données soumises dans le formulaire.
            $plainPassword = $form->getData()['password'];

            // Utilise le service de hachage de mot de passe pour hasher le mot de passe en clair.
            // $this->hasher est probablement une instance de UserPasswordHasherInterface - L'objectif principal de UserPasswordHasherInterface est de fournir une méthode standardisée pour hacher et vérifier les mots de passe des utilisateurs tout en offrant une flexibilité dans le choix de l'algorithme de hachage et des paramètres de sécurité.
            $passwordHashed = $this->hasher->hashPassword($admin, $plainPassword);

            // Définit le mot de passe haché dans l'objet $admin.
            // Cela met à jour le mot de passe de l'administrateur avec le nouveau mot de passe haché.
            $admin->setPassword($passwordHashed);

            // Définit la date et l'heure actuelles comme date de dernière mise à jour de l'administrateur.
            // Utilise DateTimeImmutable pour s'assurer que cette valeur ne peut pas être modifiée après coup.
            $admin->setUpdatedAt(new DateTimeImmutable());

            // Persiste l'entité utilisateur dans la base de données
            $this->em->persist($admin);

            // Enregistre les modifications dans la base de données
            $this->em->flush();

            // Ajoute un message flash pour informer l'utilisateur que la modification du profil a réussi
            $this->addFlash('success', "Le mot de passe a été modifié avec succès.");

            // Redirige l'utilisateur vers la page d'index du profil administrateur
            return $this->redirectToRoute('admin_profile_index');
        }

        return $this->render('pages/admin/profile/edit_password.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/profile/delete', name: 'admin_profile_delete', methods: ['POST', 'DELETE'])]
    public function deleteProfile(Request $request): Response
    {
        // Vérifie si le jeton CSRF est valide en utilisant le nom 'delete_profile_' et le jeton obtenu à partir de la requête.
        if ( $this->isCsrfTokenValid('delete_profile_', $request->request->get('_csrf_token')) )
        {
            // Récupère l'utilisateur actuellement connecté (administrateur) à partir de la méthode getUser().
            /** @var User $admin */
            $admin = $this->getUser();

            // Ajoute un message flash de succès indiquant que le profil de l'utilisateur a été supprimé avec succès.
            $this->addFlash('success', "Le profile de {$admin->getFirstName()} {$admin->getLastName()} a été supprimée avec succès.");
            
             // Efface le token de sécurité actuel (déconnecte l'utilisateur).
            $this->container->get('security.token_storage')->setToken(null);

            // Supprime l'utilisateur (administrateur) de la base de données à l'aide de l'EntityManager.
            $this->em->remove($admin);
            
            // Applique les modifications dans la base de données (commit des changements).
            $this->em->flush();


        // Redirige vers la route 'admin_profile_index' après la suppression réussie.
        return $this->redirectToRoute('admin_profile_index');
        }

        // Ajoute un message flash d'erreur si le jeton CSRF est invalide.
        $this->addFlash('error', "Le token CSRF est invalide.");
       
        // Redirige vers la route 'admin_profile_index' si le jeton CSRF est invalide.
        return $this->redirectToRoute('admin_profile_index');
    }
}
