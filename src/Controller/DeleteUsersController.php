<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DeleteUsersController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository         $userRepository
    )
    {
    }

    /**
     * @return void
     * @throws Exception
     * @description Supression de tous les utilisateurs depuis un endpoint Delete /admin/users sur l'entité User::class
     */
    public function __invoke(): void
    {
        // Récupération de tous les utilisateurs, sous forme de try catch pour gérer les possibles erreurs de récupération du repository
        try {
            $users = $this->userRepository->findAll() ?? [];
        } catch (\Exception $e) {
            throw new \Exception('An error occurred while fetching users.', 500);
        }

        // Suppression des utilisateurs
        $this->deleteUsers($users);

        // On applique les changements sur la base de données
        $this->entityManager->flush();
    }

    /**
     * @param array $users
     * @return void
     * @description Suppression des utilisateurs
     */
    private function deleteUsers(array $users): void
    {
        // Pour chaque utilisateur, on le supprime
        foreach ($users as $user) {
            $this->entityManager->remove($user);
        }
    }

}
