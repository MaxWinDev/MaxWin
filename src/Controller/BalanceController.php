<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ReloadBalanceType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Transactions;
use App\Entity\User;

#[Route('/balance')]
class BalanceController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
    ) {
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

            $transaction = new Transactions();
            $transaction->setUser($user);
            $transaction->setType('deposit');
            $transaction->setAmount($convertedAmount);
            $transaction->setCurrency($currency);
            $transaction->setDate(new \DateTime());

            $entityManager->persist($user);
            $entityManager->persist($transaction);
            $entityManager->flush();

            $error = 'Votre dépôt a été effectué avec succès.';
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
            $currency = $request->request->get('currency');
            $convertedAmount = $this->convertCurrency($amount, $currency);

            if ($convertedAmount > $user->getBalance()) {
                $error = 'Vous n\'avez pas suffisamment de solde pour effectuer ce retrait.';
            } else {
                $user->setBalance($user->getBalance() - $convertedAmount);

                $transaction = new Transactions();
                $transaction->setUser($user);
                $transaction->setType('withdrawal');
                $transaction->setAmount($convertedAmount);
                $transaction->setCurrency($currency);
                $transaction->setDate(new \DateTime());

                $entityManager->persist($user);
                $entityManager->persist($transaction);
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
