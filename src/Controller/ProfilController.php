<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfilController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
    )
    {
    }

    #[Route('/profil', name: 'app_profil')]
    public function index(): Response
    {
        $user = $this->security->getUser();
        $userWins = null;

        if ($user) {
            $userWins = $user->getWins();
        }


        return $this->render('profil/profil.html.twig', [
            'user' => $user,
            'userWins' => $userWins,
        ]);
    }

    #[Route('/balance', name: 'get_balance', methods: ['GET'])]
    public function getBalance(): Response
    {
        $user = $this->security->getUser();
        if ($user === null) {
            return new Response(json_encode(['balance' => 0]), 200, ['Content-Type' => 'application/json']);
        }
        return new Response(json_encode(['balance' => $user->getBalance()]), 200, ['Content-Type' => 'application/json']);
    }
}
