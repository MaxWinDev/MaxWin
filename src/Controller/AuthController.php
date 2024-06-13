<?php

namespace App\Controller;

use PHPUnit\Util\Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\UsersAuthenticator;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
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

    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     * @description Route de connexion
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, on le redirige vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // On récupère les erreurs de connexion
        $error = $authenticationUtils->getLastAuthenticationError();

        // On récupère le dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        // On redirige vers la page de connexion
        return $this->render('auth/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @return void
     * @description Route de déconnexion (appelé depuis le bouton "se deconnecter" dans la section profile)
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
    }

    /**
     * @param Request $request
     * @param Security $security
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        // Si l'utilisateur est déjà connecté, on le redirige vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // On crée un nouvel utilisateur
        $user = new User();

        // On crée le formulaire d'inscription
        $form = $this->createForm(RegistrationFormType::class, $user);

        // On traite la requête
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Initialisation du solde à 0
            $user->setBalance(0);

            // sauvegarde des modifications
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

            // On login l'utilisateur dans le contexte symfony
            return $security->login($user, UsersAuthenticator::class, 'app');
        }

        // On redirige vers la page d'inscription
        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**
     * @throws JWTFailureException
     * @param TokenInterface $token c'est le token généré lors de l'inscription provenant de l'email de confirmation que l'utilisateur a reçu sur sa boite mail (docker-compose avec mailpit)
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
            // Si il y a une Exception, on renvoie une erreur
            throw new JWTFailureException(
                'Error during the email verify process',
                $e->getMessage()
            );
        }

        // On redirige vers la page profile
        return $this->redirectToRoute('app_profil');
    }

    /**
     * @throws TransportExceptionInterface
     * @description Permet de supprimer un compte utilisateur en envoyant une requete sur un endpoint ApiPlatform
     */
    #[Route('/delete-account', name: 'app_delete_account')]
    public function deleteAccount(): Response
    {

        // On récupère l'utilisateur connecté dans le contexte symfony
        $user = $this->security->getUser();
        if($user instanceof User) {
            // On envoie une requete DELETE sur l'endpoint ApiPlatform pour supprimer le compte utilisateur
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
                // Si il y a une Exception, on renvoie une erreur
                throw new Exception(
                    'Error during the delete account process',
                    $e->getMessage()
                );
            }
        }

        // On redirige vers la page d'accueil
        return $this->redirectToRoute('app_home');
    }
}
