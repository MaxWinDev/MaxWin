<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ReloadBalanceType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/balance')]
class BalanceController extends AbstractController
{

    public function __construct(
        private readonly Security $security,
    )
    {
    }


    #[Route('/deposit')]
    public function deposit(Request $request, EntityManagerInterface $entityManager): Response
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

    #[Route('/withdraw', name: 'app_withdraw')]
    public function withdraw(Request $request, EntityManagerInterface $entityManager): Response
    {
        $error = null;



        $user = $this->security->getUser();
    
        $form = $this->createForm(ReloadBalanceType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $amount = $form->get('amount')->getData();
    
            if ($amount > $user->getBalance()) {
                $error = 'Vous n\'avez pas suffisamment de solde pour effectuer ce retrait.';
            }
            else{
                $currency = $request->request->get('currency');
    
                $convertedAmount = $this->convertCurrency($amount, $currency);
        
                $user->setBalance($user->getBalance() - $convertedAmount);
        
                $entityManager->persist($user);
                $entityManager->flush();

                $error = 'Votre retrait a été effectué avec succès.';
            }
    
        }
    
        return $this->render('balance/withdraw-balance.html.twig', [
            'error' => $error,
            'form' => $form->createView(),
        ]);
    }

    private function convertCurrency($amount, $currency)
    {
        switch ($currency) {
            case 'USD':
                return $amount * 1.1;
            case 'GBP':
                return $amount * 0.9;
            case 'EUR':
            default:
                return $amount;
        }
    }
}