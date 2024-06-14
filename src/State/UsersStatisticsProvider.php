<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\UsersStatisticsDto;

class UsersStatisticsProvider implements ProviderInterface
{
    public function __construct(
        private readonly CollectionProvider $collectionProvider,
    )
    {
    }

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object|array|null
     * @description cette méthode est surchargée pour fournir les statistiques des utilisateurs
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // On récupère les users de l'application et on les transforme en type array
        $usersData = $this->collectionProvider->provide($operation, $uriVariables, $context);
        $users = iterator_to_array($usersData);

        // On crée notre output
        $usersStats = new UsersStatisticsDto();

        // On remplit les données de notre output
        $usersStats->numberOfUsers = count($users);
        $usersStats->numbersOfVerifiedEmails = count(array_filter($users, fn($user) => $user->isVerified()));
        $usersStats->numbersOfUnverifiedEmails = count(array_filter($users, fn($user) => !$user->isVerified()));
        $usersStats->totalBalance = array_reduce($users, fn($total, $user) => $total + $user->getBalance(), 0);

        // On retourne notre output
        return $usersStats;
    }
}
