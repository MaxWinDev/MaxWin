<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/game')]
class GameController extends AbstractController
{
    #[Route('/', name: 'game', methods: ['GET'])]
    public function viewGame() {
        return $this->render('game/game.html.twig');
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

        // Calculer les gains
        $touchingSymbols = $this->calculateWins($symbols);

        foreach ($touchingSymbols as $lineIndex => $indices) {
            echo "Fruits qui se touchent sur la ligne $lineIndex : " . implode(', ', $indices) . "\n";
        }

        // Retourner une réponse JSON avec les gains
        return new Response(json_encode(['wins' => $touchingSymbols]), 200, ['Content-Type' => 'application/json']);
    }

    private function calculateWins(array $symbols): array
    {
        $touchingSymbols = []; // Tableau pour stocker les indices des symboles qui se touchent

        foreach ($symbols as $reel) {
            $prevSymbol = null; // Stocke le symbole précédent dans la ligne
            $consecutiveCount = 0; // Compte le nombre de symboles consécutifs identiques

            $touchingIndices = []; // Tableau pour stocker les indices des symboles qui se touchent dans cette ligne

            foreach ($reel as $index => $symbol) {
                if ($prevSymbol !== null && $symbol === $prevSymbol) {
                    // Si le symbole actuel est identique au précédent, incrémente le compteur de symboles consécutifs
                    $consecutiveCount++;

                    // Si deux symboles identiques sont côte à côte, ajoute leurs indices au tableau
                    if ($consecutiveCount === 2) {
                        $touchingIndices[] = $index - 1; // Index du premier symbole touché
                        $touchingIndices[] = $index; // Index du deuxième symbole touché
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