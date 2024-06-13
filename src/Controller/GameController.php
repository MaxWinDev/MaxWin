<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\SecurityBundle\Security;


#[Route('/game')]
class GameController extends AbstractController
{

    var array $symbolPayouts = [
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
        private readonly HttpClientInterface    $client,
        private readonly Security               $security,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/', name: 'game', methods: ['GET'])]
    public function viewGame()
    {
        $user = $this->security->getUser();

        $response = $this->render('game/game.html.twig', [
            'user' => $user
        ]);

        // Desactivate cache
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-store', true);
        return $response;
    }

    #[Route('/check_wins', name: 'check_wins', methods: ['POST'])]
    public function checkWins(Request $request): Response
    {
        $user = $this->security->getUser();
        $user->setBalance($user->getBalance() - 1);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Lire le contenu JSON de la requête
        $data = json_decode($request->getContent(), true);

        // Vérifiez que les données ont bien été reçues
        if (!isset($data['symbols'])) {
            return new Response(json_encode(['error' => 'Invalid data']), 400, ['Content-Type' => 'application/json']);
        }

        $symbols = $data['symbols'];

        // Initialiser un tableau pour stocker les résultats des gains
        $wins = [];

        // Calculer les gains
        $touchingSymbols = $this->calculateWins($symbols);

        foreach ($touchingSymbols as $lineIndex => $indices) {
            if (reset($indices) === 0) {
                $touchingFruits = [];

                // Récupérer les fruits associés aux indices dans la ligne
                foreach ($indices as $index) {
                    $touchingFruits[] = $symbols[$lineIndex][$index];
                }

                // Compter le nombre d'occurrences de chaque couple de fruits
                $fruitCounts = array_count_values($touchingFruits);

                // Stocker les couples fruits/nombre dans le tableau des gains
                foreach ($fruitCounts as $fruit => $count) {
                    $wins[] = ['symbol' => $fruit, 'count' => $count];
                }
            }
        }

        if (count($wins) !== 0) {
            $this->calculatePayout($wins, 1);
        }
        return new Response('', 200);
    }


    private function calculateWins(array $symbols): array
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

    function calculatePayout($wins, $bet)
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