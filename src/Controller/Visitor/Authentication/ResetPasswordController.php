<?php

namespace App\Controller\Visitor\Authentication;


use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Afficher et traiter le formulaire pour demander une réinitialisation du mot de passe.
     */
    #[Route('', name: 'visitor_authentication_forgot_password_request', methods: ['GET', 'POST'])]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer,
                $translator
            );
        }
      
        return $this->render('pages/visitor/authentication/reset_password/request.html.twig', [
            'requestForm' => $form,
        ]);
    }

    /**
     * Page de confirmation après qu'un utilisateur a demandé une réinitialisation du mot de passe.
     */
    #[Route('/check-email', name: 'visitor_authentication_check_email')]
    public function checkEmail(): Response
    {
         // Générer un token factice si l'utilisateur n'existe pas ou si quelqu'un accède directement à cette page.
        // Cela évite de révéler si un utilisateur a été trouvé avec l'adresse e-mail donnée ou non.
        if (null === ($resetToken = $this->getTokenObjectFromSession())) 
        {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('pages/visitor/authentication/reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Valide et traite l'URL de réinitialisation que l'utilisateur a cliquée dans son email.
     */
    #[Route('/reset/{token}', name: 'visitor_authentication_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, ?string $token = null): Response
    {
        if ($token) 
        {
            // Nous stockons le token en session et le retirons de l'URL, pour éviter que l'URL ne soit chargée
            // dans un navigateur et potentiellement exposée à des scripts tiers.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('visitor_authentication_reset_password');
        }

        $token = $this->getTokenFromSession();

        if (null === $token)
         {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try 
        {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } 
        catch (ResetPasswordExceptionInterface $e) 
        {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));

            return $this->redirectToRoute('visitor/authentication_forgot_password_request');
        }

        // Le token est valide ; permettre à l'utilisateur de changer son mot de passe.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            // Un token de réinitialisation de mot de passe ne doit être utilisé qu'une seule fois, nous le supprimons donc.
            $this->resetPasswordHelper->removeResetRequest($token);

            // Encode(hash) le mot de passe en clair et le définit.
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            // La session est nettoyée après modification du mot de passe.
            $this->cleanSessionAfterReset();

            $this->addFlash('success', 'Le mot de passe a été réinitialisé. Vous pouvez vous connecter');
            return $this->redirectToRoute('visitor_authentication_login');
        }

        return $this->render('pages/visitor/authentication/reset_password/reset.html.twig', [
            'resetForm' => $form,
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Ne pas révéler si un compte utilisateur a été trouvé ou non.
        if (!$user) 
        {
            return $this->redirectToRoute('visitor_authentication_check_email');
        }

        try 
        {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } 
        catch (ResetPasswordExceptionInterface $e) 
        {
            // Si vous voulez informer l'utilisateur pourquoi un e-mail de réinitialisation n'a pas été envoyé, décommentez
            // les lignes ci-dessous et changez la redirection vers 'app_forgot_password_request'.
            // Attention : Cela peut révéler si un utilisateur est enregistré ou non.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     '%s - %s',
            //     $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, [], 'ResetPasswordBundle'),
            //     $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            // ));

            return $this->redirectToRoute('visitor_authentication_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('roastbeans@gmail.com', 'Pierre Dubois'))
            ->to($user->getEmail())
            ->subject('Votre demande de réinitialisation du mot de passe.')
            ->htmlTemplate('emails/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $mailer->send($email);

        // Stocker l'objet token en session pour récupération dans la route check-email.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('visitor_authentication_check_email');
    }
}
