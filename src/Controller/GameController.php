<?php

namespace App\Controller;

use App\Service\GameService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\SecurityBundle\Security;


#[Route('/game')]
class GameController extends AbstractController
{

    public function __construct(
        private readonly Security               $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly GameService            $gameService
    )
    {
    }

    /**
     * @return void
     * @description Route pour la page de jeu
     */
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

    /**
     * @return Response
     * @description Route appelé depuis le JS, pour vérifier les gains de l'utilisateur,
     * décrémenter sa balance (prix du spin) et si il y a des gains, lui ajouter à sa balance
     */
    #[Route('/check_wins', name: 'check_wins', methods: ['POST'])]
    public function checkWins(Request $request): Response
    {
        // Décrémenter le solde de l'utilisateur de 1 pour chaque spin
        $user = $this->security->getUser();

        // On vérifie si l'utilisateur a assez pour pouvoir jouer, sinon on lui ajouter 100 à sa balance
        if($user->getBalance() < 1){
            $user->setBalance($user->getBalance() + 100);
        } else {
            $user->setBalance($user->getBalance() - 1);
        }

        // Sauvegarder le nouvel état du solde de l'utilisateur en base de données
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
        $touchingSymbols = $this->gameService->calculateWins($symbols);

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

        // Si la liste de gains n'est pas vide 
        if (count($wins) !== 0) {
            $this->gameService->calculatePayout($wins, 1); // On ajoute à la balance du client le gain associé, ici avec une mise de 1
        }
        return new Response('', 200);
    }
}