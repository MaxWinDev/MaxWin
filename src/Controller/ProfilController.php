<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ProfilManager;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(ProfilManager $profilManager): Response
    {
        $user = $profilManager->getUserByID(1);
        // console.log($user);
        //var_dump($user);

        return $this->render('profil/index.html.twig', [
            'user' => $user,
        ]);
    }
}
