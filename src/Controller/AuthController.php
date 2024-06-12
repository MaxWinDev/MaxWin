<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\UsersAuthenticator;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/auth')]
class AuthController extends AbstractController
{

    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly JWTTokenManagerInterface    $jwtManager,
        private readonly MailerService               $mailerService,
        private readonly EntityManagerInterface      $entityManager,
        private readonly Security                    $security,
        private readonly HttpClientInterface         $httpClient,
        private readonly UrlGeneratorInterface       $urlGenerator
    )
    {
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setBalance(0);

            // save the user into
            $entityManager->persist($user);
            $entityManager->flush();

            // On génère le token JWT pour la vérification de l'adresse emails avec le jwt lexik bundle
            $token = $this->jwtManager->createFromPayload($user, ['action' => 'confirm_email']);

            // On envoie un emails de confirmation à l'utilisateur
            $this->mailerService->sendEmail(
                $user->getEmail(),
                'Confirmation de votre addresse email',
                'emails/confirmation_email.html.twig',
                [
                    'token' => $token,
                    'username' => $user->getUsername(),
                ]
            );

            // do anything else you need here, like send an emails

            return $security->login($user, UsersAuthenticator::class, 'app');
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**
     * @throws JWTFailureException
     */
    #[Route('/verify/{token}', name: 'app_verify_email')]
    public function verifyUserEmail(TokenInterface $token): Response
    {
        try {
            // on vérifie si le token est valide (cohérent, pas expiré et signature correcte)
            $user = $token->getUser();
            if($user instanceof User && $user->isVerified() === false) {
                $user->setVerified(true);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        } catch (\Exception $e) {
            throw new JWTFailureException(
                'Error during the email verify process',
                $e->getMessage()
            );
        }

        return $this->redirectToRoute('app_profil');
    }

    /**
     * @throws TransportExceptionInterface
     * @description Permet de supprimer un compte utilisateur en envoyant une requete sur un endpoint ApiPlatform
     */
    #[Route('/delete-account', name: 'app_delete_account')]
    public function deleteAccount(): Response
    {

        $user = $this->security->getUser();
        if($user instanceof User) {
            try {
                $this->httpClient->request(
                    'DELETE',
                    $this->urlGenerator->generate('api_users_delete_item', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    [
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ]
                    ]
                );
            } catch (\Exception $e) {
                throw new Exception(
                    'Error during the delete account process',
                    $e->getMessage()
                );
            }
        }
        return $this->redirectToRoute('app_home');
    }
}
