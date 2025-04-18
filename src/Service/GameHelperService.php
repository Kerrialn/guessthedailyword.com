<?php

namespace App\Service;

final readonly class GameHelperService
{
    public function checkGuess(string $guessWord, string $dailyWord) : bool
    {
        return strtolower($guessWord) === strtolower($dailyWord);
    }

}