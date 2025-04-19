<?php

namespace App\Service;

final readonly class GameHelperService
{
    public function checkGuess(string $guessWord, string $dailyWord): bool
    {
        return strtolower($guessWord) === strtolower($dailyWord);
    }

    /**
     * @param string $guess
     * @param string $actual
     * @return string[]
     */
    public function generateLetterFeedback(string $guess, string $actual): array
    {
        $guess  = strtoupper($guess);
        $actual = strtoupper($actual);

        $result = array_fill(0, strlen($actual), 'absent');
        $used   = array_fill(0, strlen($actual), false);

        // First pass: mark correct letters (green)
        for ($i = 0; $i < strlen($guess); $i++) {
            if ($guess[$i] === $actual[$i]) {
                $result[$i] = 'correct';
                $used[$i]   = true;
            }
        }

        // Second pass: mark present-but-wrong-position letters (yellow)
        for ($i = 0; $i < strlen($guess); $i++) {
            if ($result[$i] !== 'correct') {
                for ($j = 0; $j < strlen($actual); $j++) {
                    if (!$used[$j] && $guess[$i] === $actual[$j]) {
                        $result[$i] = 'present';
                        $used[$j]   = true;
                        break;
                    }
                }
            }
        }

        return $result;
    }

}