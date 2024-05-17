<?php

namespace App\Controller\Visitor\Registration;



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
            return $this->redirectToRoute('visitor_home_index');
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
            // 6- Encoder le mot de passe
            $passwordHashed = $userPasswordHasher->hashPassword($user, $form->get('password')->getData());

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

    #[Route('/register/waiting-for-email-verification', name: 'visitor_registration_waiting_for_email_verification', methods:['GET'])]
    public function waitingForEmailVerification(): Response
    {
        return $this->render('pages/visitor/registration/waiting_for_email_verification.html.twig');
    }

    #[Route('/verify/email', name: 'visitor_registration_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) 
        {
            return $this->redirectToRoute('visitor_registration_register');
        }

        $user = $userRepository->find($id);

        if (null === $user)
        {
            return $this->redirectToRoute('visitor_registration_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try 
        {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } 
        catch (VerifyEmailExceptionInterface $exception) 
        {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('visitor_registration_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre adresse email a été vérifiée, vous pouvez vous connecter');

        return $this->redirectToRoute('visitor_authentication_login');
    }
}
