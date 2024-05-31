<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Entity\User;

class ProfilManager{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository
    )
    {}

    public function getUserByID(int $id): User{
        return $this->userRepository->find($id);
    }
}