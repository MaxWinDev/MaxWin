<?php
// create controller for the home page

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/home")]
class HomeController extends AbstractController
{
    #[Route("/")]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}





