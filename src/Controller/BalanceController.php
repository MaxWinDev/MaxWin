<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ReloadBalanceType;

#[Route('/balance')]
class BalanceController extends AbstractController
{

    public function __construct(
        private readonly Security $security,
    )
    {
    }



    #[Route('/recharge')]
    public function recharge(Request $request, EntityManagerInterface $entityManager): Response
    {
        $error = null;

        $user = $this->security->getUser();
    
        $form = $this->createForm(ReloadBalanceType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $amount = $form->get('amount')->getData();
    
        
            $currency = $request->request->get('currency');

            $convertedAmount = $this->convertCurrency($amount, $currency);
    
            $user->setBalance($user->getBalance() + $convertedAmount);
    
            $entityManager->persist($user);
            $entityManager->flush();

            $error = 'Votre depot a été effectué avec succès.';
        
    
        }
    
        return $this->render('balance/add-balance.html.twig', [
            'error' => $error,
            'form' => $form->createView(),
        ]);
    }
}
