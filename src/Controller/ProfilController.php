<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfilController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
    )
    {
    }

    #[Route('/profil', name: 'app_profil')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): Response
    {
        $user = $this->security->getUser();
        $userWins = null;

        if ($user) {
            $userWins = $user->getWins();
        }


        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'userWins' => $userWins,
        ]);
    }
}
