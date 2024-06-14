<?php

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\Groups;

class UsersStatisticsDto
{
    /**
     * @var int|null
     * @description Nombre total d'utilisateurs de l'application
     */
    #[Groups(['user:read'])]
    public ?int $numberOfUsers;

    /**
     * @var int|null
     * @description Nombre d'emails vérifiés
     */
    #[Groups(['user:read'])]
    public ?int $numbersOfVerifiedEmails;

    /**
     * @var int|null
     * @description Nombre d'emails non vérifiés
     */
    #[Groups(['user:read'])]
    public ?int $numbersOfUnverifiedEmails;

    /**
     * @var int|null
     * @description Solde total de tous les utilisateurs
     */
    #[Groups(['user:read'])]
    public ?int $totalBalance;
}