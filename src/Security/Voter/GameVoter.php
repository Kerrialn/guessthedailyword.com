<?php

namespace App\Security\Voter;

use App\Entity\DailyWord;
use App\Entity\Guess;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class GameVoter extends Voter
{
    public const CAN_GUESS = 'CAN_GUESS';

    /**
     * @param string $attribute
     * @param DailyWord $subject
     * @return bool
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CAN_GUESS])
            && $subject instanceof DailyWord;
    }

    /**
     * @param string $attribute
     * @param DailyWord $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
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
            fn(Guess $guess) => $guess->getOwner() === $user
        );

        $hasCorrectGuess = $userGuesses->exists(
            fn($key, Guess $guess) => $guess->getIsCorrect()
        );

        if ($hasCorrectGuess) {
            return false;
        }

        $incorrectGuessesCount = $userGuesses->filter(
            fn(Guess $guess) => !$guess->getIsCorrect()
        )->count();

        return $incorrectGuessesCount < 3;
    }
}
