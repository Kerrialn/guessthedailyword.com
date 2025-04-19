<?php

namespace App\Security\Voter;

use App\Entity\DailyWord;
use App\Entity\Guess;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, DailyWord>
 */
final class GameVoter extends Voter
{
    public const CAN_GUESS = 'CAN_GUESS';

    /**
     * @param DailyWord $subject
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::CAN_GUESS
            && $subject instanceof DailyWord;
    }

    /**
     * @param DailyWord $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (! $user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::CAN_GUESS => $this->canUserGuess($subject, $user),
            default => false
        };

    }

    private function canUserGuess(DailyWord $dailyWord, User $user): bool
    {
        $userGuesses = $dailyWord->getGuesses()->filter(
            fn(Guess $guess): bool => $guess->getOwner() === $user
        );

        $hasCorrectGuess = $userGuesses->exists(
            fn($key, Guess $guess): bool => $guess->getIsCorrect()
        );

        if ($hasCorrectGuess) {
            return false;
        }

        $incorrectGuessesCount = $userGuesses->filter(
            fn(Guess $guess): bool => ! $guess->getIsCorrect()
        )->count();

        return $incorrectGuessesCount < 3;
    }
}
