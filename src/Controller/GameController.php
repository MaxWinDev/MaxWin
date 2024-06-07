<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/game')]
class GameController extends AbstractController
{
    
    public function __construct(
        private readonly HttpClientInterface $client
    ) {}
    
    #[Route('/', name: 'game', methods: ['GET'])]
    public function viewGame() {
        return $this->render('game/game.html.twig');
    }

    #[Route('/send_wins', name: 'send_wins', methods: ['POST'])]
    public function get_wins(Request $request) : Response {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/check_wins', name: 'check_wins', methods: ['POST'])]
    public function checkWins(Request $request): Response
    {
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

        var_dump($wins);
        if (count($wins) !== 0) {
            // Envoyer les gains via une requête POST avec HttpClient, si une win est détéctée
            $this->client->request('POST', 'http://127.0.0.1:8000/game/send_wins', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(['wins' => $wins]),
            ]);
            // Retourner une réponse JSON avec les gains
            //return new Response(json_encode(['wins' => $wins]), 200, ['Content-Type' => 'application/json']);
        } else {
            // Si aucun gain n'a été obtenu, retournez une réponse vide
            return new Response('', 200);
        }
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
                        if($consecutiveCount === 2){
                            $touchingIndices[] = $index - 1; // Index du premier symbole touché
                            $touchingIndices[] = $index; // Index du deuxième symbole touché
                        }
                        else{
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
}