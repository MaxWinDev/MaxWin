<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class GameService
{
    private array $symbolPayouts = [
        '7' => 50,
        'cerise' => 5,
        'citron' => 5,
        'fraise' => 5,
        'gold' => 25,
        'max' => 100,
        'pasteque' => 5,
        'prune' => 5,
    ];

    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function calculateWins(array $symbols): array
    {
        $touchingSymbols = [];

        foreach ($symbols as $reel) {
            $firstSymbol = reset($reel); // Stocke le premier symbole de la ligne
            $prevSymbol = null;
            $consecutiveCount = 0;

            $touchingIndices = [];

            foreach ($reel as $index => $symbol) {
                if ($prevSymbol !== null && $symbol === $prevSymbol && $symbol === $firstSymbol) {
                    // Si le symbole actuel est identique au précédent et au premier symbole, incrémente le compteur de symboles consécutifs
                    $consecutiveCount++;

                    // Si deux symboles identiques sont côte à côte, ajoute leurs indices au tableau
                    if ($consecutiveCount > 1) {
                        if ($consecutiveCount === 2) {
                            $touchingIndices[] = $index - 1; // Index du premier symbole touché
                            $touchingIndices[] = $index; // Index du deuxième symbole touché
                        } else {
                            $touchingIndices[] = $index; // Index du deuxième symbole touché
                        }
                    }
                } else {
                    // Réinitialise le compteur si le symbole actuel est différent du précédent
                    $consecutiveCount = 1;
                }

                $prevSymbol = $symbol; // Met à jour le symbole précédent
            }

            // Ajoute les indices des symboles touchés dans cette ligne au tableau principal
            $touchingSymbols[] = $touchingIndices;
        }

        return $touchingSymbols;
    }

    public function calculatePayout(array $wins, int $bet): void
    {
        $totalPayout = 0;

        foreach ($wins as $win) {
            $totalPayout += $this->symbolPayouts[$win['symbol']] * $win['count'] * $bet;
        }

        $user = $this->security->getUser();
        $user->setBalance($user->getBalance() + $totalPayout);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
