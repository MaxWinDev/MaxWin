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

    public const CAN_DELETE = 'CAN_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if ($attribute != self::CAN_DELETE) {
            return false;
        }

        // only vote on Delete objects
        if (!$subject instanceof Delete) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if(!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::CAN_DELETE => $this->canDelete($user),
            default => false,
        };
    }

    private function canDelete(User $user): bool
    {
        return $user->getId() === $this->securityService->getUser()->getId();
    }
}