<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class GameService
{
    // Tableau des multiplicateurs de gains pour chaque symbole
    private array $symbolPayouts = [
        '7' => 25,
        'cerise' => 2,
        'citron' => 2,
        'fraise' => 2,
        'gold' => 12,
        'max' => 50,
        'pasteque' => 2,
        'prune' => 2,
    ];

    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return Array
     * @param array $symbols
     * @description Cacul les symboles gagnant (2 identique au minimum qui se touchent depuis la gauche)
     */
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

    /**
     * @return void
     * @description Ajoute à la balance de l'utilisateur ses différents gains
     */
    public function calculatePayout(array $wins, int $bet): void
    {
        $totalPayout = 0;

        // Calcule le montant total des gains
        foreach ($wins as $win) {
            $totalPayout += $this->symbolPayouts[$win['symbol']] * $win['count'] * $bet;
        }

        // Met à jour le solde de l'utilisateur
        $user = $this->security->getUser();
        $user->setBalance($user->getBalance() + $totalPayout);

        // Sauvegarde le nouvel état du solde de l'utilisateur en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
