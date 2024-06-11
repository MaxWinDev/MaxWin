<?php
// create controller for the home page

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/home")]
class HomeController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
    )
    {
    }

    #[Route("/", name: 'app_home')]
    public function index(): Response
    {
        $user = $this->security->getUser();
        return $this->render('index.html.twig', [
            'user' => $user,
            'controller_name' => 'HomeController',
        ]);
    }
}





