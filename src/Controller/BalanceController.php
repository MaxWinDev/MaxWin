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

    /**
     * @return Response
     * @description Route pour deposer de l'argent sur le compte de l'utilisateur
     */
    #[Route('/deposit', name: 'app_deposit')]
    public function deposit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $error = null;
        $user = $this->security->getUser();
        $form = $this->createForm(ReloadBalanceType::class);
        $form->handleRequest($request);

        // On vérifie que le formulaire à été soumis & est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les données du formulaire
            $amount = $form->get('amount')->getData();
            $currency = $request->request->get('currency');
            $convertedAmount = $this->convertCurrency($amount, $currency); // On convertis le montant en EUR

            $user->setBalance($user->getBalance() + $convertedAmount); // On incrémente la balance

            // On créer une nouvelle transaction pour pouvoir traçer le dépôt
            $transaction = new Transactions();
            $transaction->setUser($user);
            $transaction->setType('deposit');
            $transaction->setAmount($convertedAmount);
            $transaction->setCurrency($currency);
            $transaction->setDate(new \DateTime());

            // On sauvegarde en base de données
            $entityManager->persist($user);
            $entityManager->persist($transaction);
            $entityManager->flush();

            $error = 'Votre dépôt a été effectué avec succès.';
        }

        return $this->render('balance/add-balance.html.twig', [
            'error' => $error,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @return Response
     * @description Route pour retirer de l'argent du compte de l'utilisateur
     */
    #[Route('/withdraw', name: 'app_withdraw')]
    public function withdraw(Request $request, EntityManagerInterface $entityManager): Response
    {
        $error = null;
        $user = $this->security->getUser();
        $form = $this->createForm(ReloadBalanceType::class);
        $form->handleRequest($request);

        // On vérifie que le formulaire à été soumis & est valide
        if ($form->isSubmitted() && $form->isValid()) {
            $amount = $form->get('amount')->getData();
            $currency = $request->request->get('currency');
            $convertedAmount = $this->convertCurrency($amount, $currency);  // On convertis le montant en EUR

            // On vérifie si l'utilisateur a suffisemment d'argent sur son compte pour le retirer
            if ($convertedAmount > $user->getBalance()) {
                $error = 'Vous n\'avez pas suffisamment de solde pour effectuer ce retrait.';
            } else {
                $user->setBalance($user->getBalance() - $convertedAmount); // On enlève le montant qu'il souhaite retirer

                // On créer une nouvelle transaction pour pouvoir traçer le retrait
                $transaction = new Transactions();
                $transaction->setUser($user);
                $transaction->setType('withdrawal');
                $transaction->setAmount($convertedAmount);
                $transaction->setCurrency($currency);
                $transaction->setDate(new \DateTime());

                // On sauvegarde en base de données
                $entityManager->persist($user);
                $entityManager->persist($transaction);
                $entityManager->flush();

                $error = 'Votre retrait a été effectué avec succès.';
            }
        }

        return $this->render('balance/withdraw-balance.html.twig', [
            'error' => $error,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @return number
     * @description Converti la somme en fonction de la devise choisie
     */
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
