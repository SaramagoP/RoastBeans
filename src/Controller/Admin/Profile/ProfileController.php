<?php

namespace App\Controller\Admin\Profile;


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
        /** @var User */
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

            $plainPassword = $form->getData()['password'];

            $passwordHashed = $this->hasher->hashPassword($admin, $plainPassword);

            $admin->setPassword($passwordHashed);

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
        if ( $this->isCsrfTokenValid('delete_profile_', $request->request->get('_csrf_token')) )
        {
            /** @var User */
            $admin = $this->getUser();

            $this->addFlash('success', "Le profile de {$admin->getFirstName()} {$admin->getLastName()} a été supprimée avec succès.");
            
            $this->container->get('security.token_storage')->setToken(null);

            $this->em->remove($admin);
            
            $this->em->flush();

        return $this->redirectToRoute('admin_profile_index');
        }
    }
}
