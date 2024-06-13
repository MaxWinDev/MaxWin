<?php

namespace App\Voters;

use ApiPlatform\Metadata\Delete;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class UserVoter extends Voter
{

    public function __construct(
        private readonly Security $securityService
    )
    {
    }

    /**
     * @const string CAN_DELETE permet de vérifier si l'utilisateur peut supprimer son propre compte. Est appelé dans les is_granted dans les endpoints voulus
     */
    public const CAN_DELETE = 'CAN_DELETE';

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     * @description Cette méthode permet de vérifier si la requête/objet est supporté par le voter
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // On vérifie si la constante passé dans le is_granted est supportée par le voter
        if ($attribute != self::CAN_DELETE) {
            return false;
        }

        // On vérifie si la requête qui appel le voter est bien une requête de type HTTP/DELETE
        if (!$subject instanceof Delete) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool
     * @description On arrive dans cette méthode uniquement si la méthode supports a renvoyé true. Cette méthode permet de vérifier si l'utilisateur peut effectuer l'action demandée
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // On récupère l'utilisateur qui a fait la requête (cf le champs object passé dans le is_granted)
        $user = $token->getUser();

        // Si le champs $user n'est pas une instance de type User, on renvoie false, la requête sera donc refusée
        if(!$user instanceof User) {
            return false;
        }

        // On regarde pour chaque constante si l'utilisateur peut effectuer l'action demandée
        return match ($attribute) {
            self::CAN_DELETE => $this->canDelete($user),
            default => false,
        };
    }

    /**
     * @param User $user
     * @return bool
     * @description Cette méthode vérifie si l'utilisateur courant du contexte d'authentification Symfony correspond au user passé en paramètre (c'est à dire celui que l'on veut supprimer)
     */
    private function canDelete(User $user): bool
    {
        // On fait une vérification de valeur et de type
        return $user->getId() === $this->securityService->getUser()->getId();
    }
}