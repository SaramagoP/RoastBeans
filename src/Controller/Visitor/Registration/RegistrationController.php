<?php

namespace App\Controller\Visitor\Registration;



use DateTimeImmutable;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

#[Route('/register', name: 'visitor_registration_register', methods:['GET', 'POST'])]
public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
{
    // Vérifier si l'utilisateur est déjà connecté. 
        //Il n'a plus rien à faire sur la page de connexion
            // Rediriger l'utilisateur vers la page d'accueil
    if ($this->getUser()) 
    {
        return $this->redirectToRoute('visitor_welcome_index');
    }
    
    // 1 -Créér l'utilisateur à insérer en base de donées
    $user = new User();

    // 2- Créer le formulaire d'inscription
    $form = $this->createForm(RegistrationFormType::class, $user);

    // 4- Associer au formulaire les données de la requête
    $form->handleRequest($request);

    // 5- Poser la condition si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) 
    {

        $user->setCreatedAt(new DateTimeImmutable());
        $user->setUpdatedAt(new DateTimeImmutable());

        // 6- Encoder le mot de passe
        $passwordHashed = $userPasswordHasher->hashPassword($user, $form->get('password')->getData()); // $user Il s'agit de l'objet utilisateur pour lequel le mot de passe doit être hashé. // $form->get('password')->getData(): Cela récupère la valeur du champ de formulaire contenant le mot de passe brut fourni par l'utilisateur. Le mot de passe est récupéré du formulaire et non directement du texte brut pour des raisons de sécurité. // $passwordHashed: C'est la variable dans laquelle le mot de passe hashé sera stocké après avoir été généré par hashPassword().

        // 7- Mettre à jour le mot de pass de l'utilisateur
        $user->setPassword($passwordHashed);

        // 8- Demander au manager des entités de préparer la requête d'insertion de l'utilisateur qui s'inscrit en base de données
        $entityManager->persist($user);

        // 9- Exécuter la requête
        $entityManager->flush();

        // 10- Envoyer l'email de verification du compte à l'utilisateur
        $this->emailVerifier->sendEmailConfirmation('visitor_registration_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('roastbeans@gmail.com', 'Pierre Dubois'))
                ->to($user->getEmail())
                ->subject('Veuillez confirmer votre email')
                ->htmlTemplate('emails/confirmation_email.html.twig')
        );

        // 11- Rediriger l'utilisateur vers la page d'accueil
        return $this->redirectToRoute('visitor_registration_waiting_for_email_verification');
    }

    // 3- Passer le formulaire à la page pour affichage
    return $this->render('pages/visitor/registration/register.html.twig', [
        'registrationForm' => $form->createView(),
    ]);
}

#[Route('/register/waiting-for-email-verification', name: 'visitor_registration_waiting_for_email_verification', methods:['GET'])] // Définition de la route pour la page en attente de vérification de l'email.
public function waitingForEmailVerification(): Response // Méthode pour afficher la page en attente de vérification de l'email.
{
    return $this->render('pages/visitor/registration/waiting_for_email_verification.html.twig'); // Rendu de la vue pour la page en attente de vérification de l'email.
}

#[Route('/verify/email', name: 'visitor_registration_verify_email')] // Définition de la route pour vérifier l'email.
public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response // Méthode pour vérifier l'email de l'utilisateur.
{
    $id = $request->query->get('id'); // Récupération de l'ID de l'utilisateur à partir des paramètres de la requête.

    if (null === $id) // Vérification si l'ID est null.
    {
        return $this->redirectToRoute('visitor_registration_register'); // Redirection vers la page d'inscription si l'ID est null.
    }

    $user = $userRepository->find($id); // Recherche de l'utilisateur par son ID.

    if (null === $user) // Vérification si l'utilisateur est null.
    {
        return $this->redirectToRoute('visitor_registration_register'); // Redirection vers la page d'inscription si l'utilisateur n'est pas trouvé.
    }

    // Valider le lien de confirmation de l'email, définit User::isVerified=true et persiste les changements
    try 
    {
        $this->emailVerifier->handleEmailConfirmation($request, $user); // Gestion de la confirmation de l'email.
    } 
    catch (VerifyEmailExceptionInterface $exception) // Capture des exceptions liées à la vérification de l'email.
    {
        $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle')); // Ajout d'un message flash d'erreur.

        return $this->redirectToRoute('visitor_registration_register'); // Redirection vers la page d'inscription en cas d'erreur.
    }

    // Modification de la redirection en cas de succès et gestion ou suppression du message flash dans vos templates
    $this->addFlash('success', 'Votre adresse email a été vérifiée, vous pouvez vous connecter'); // Ajout d'un message flash de succès.

    return $this->redirectToRoute('visitor_authentication_login'); // Redirection vers la page de connexion après vérification réussie.
}
}