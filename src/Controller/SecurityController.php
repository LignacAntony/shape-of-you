<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    #[Route('/forgot', name: 'auth_forgot_get', methods: ['GET'])]
    public function forgotGet(): Response
    {
        return $this->render('security/forgot.html.twig');
    }

    #[Route('/forgot', name: 'auth_forgot_post', methods: ['POST'])]
    public function forgot(
        Request $request,
        UserRepository $userRepository,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator,
    ): Response {
        $email = $request->get('_email');
        $user = $userRepository->findOneByEmail($email);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé');
            return $this->redirectToRoute('auth_forgot_get');
        }

        $token = $tokenGenerator->generateToken();
        $user->setResetToken($token);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $emailMessage = (new Email())
            ->from('onboarding@resend.dev')
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html(sprintf(
                '<p>Bonjour %s,</p>
                <p>Pour réinitialiser votre mot de passe, cliquez sur le lien suivant :</p>
                <a href="%s">Réinitialiser votre mot de passe</a>',
                htmlspecialchars($user->getUsername()),
                htmlspecialchars($url)
            ));
        
        try {
            $mailer->send($emailMessage);
            $this->addFlash('success', 'Email envoyé avec succès !');
            return $this->redirectToRoute('app_home');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
            return $this->redirectToRoute('auth_forgot_get');
        }
    }

    #[Route('/forgot-password/{token}', name: 'reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $userRepository->findOneByResetToken($token);



        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setResetToken(null);
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Mot de passe mis à jour avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset.html.twig', [
            'passForm' => $form->createView(),
        ]);
    }
}
