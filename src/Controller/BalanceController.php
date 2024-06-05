<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ReloadBalanceType;

#[Route('/balance')]
class BalanceController extends AbstractController
{
    #[Route('/recharge')]
    public function recharge()
    {
        $form = $this->createForm(ReloadBalanceType::class);

        // $form->handleRequest($request);
        // if ($form->isSubmitted() && $form->isValid()) {
        //     $amount = $form->getData()['amount'];

        //     $user = $this->getUser();
        //     $user->setBalance($user->getBalance() + $amount);

        //     //$entityManager = $this->getDoctrine()->getManager();
        //     //$entityManager->persist($user);
        //     //$entityManager->flush();

        //     $this->addFlash('success', 'Votre compte a été rechargé avec succès.');
        //     return $this->redirectToRoute('account_recharge');
        // }

        return $this->render('balance/add-balance.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
