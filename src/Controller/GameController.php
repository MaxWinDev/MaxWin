<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/game')]
class GameController extends AbstractController
{
    #[Route('/', name: 'game', methods: ['GET'])]
    public function viewGame() {

        $images = [
            "7.png",
            "fraise.png",
            "gold.png",
            "max.png",
            "pasteque.png",
            "prune.png",
            "citron.png",
            "cerise.png"
        ];

        $gridImages = [];
        for ($i = 0; $i < 25; $i++) {
            $gridImages[] = $images[array_rand($images)];
        }

        return $this->render('game/game.html.twig', [
            'gridImages' => $gridImages
        ]);
    }
}