<?php

namespace App\Service;

use Carbon\CarbonImmutable;

final readonly class PointsCalculatorService
{
    public function calculatePoints(
        CarbonImmutable $guessTime,
        CarbonImmutable $dailyWordTime,
        int $maxPoints = 100,
        int $maxTime = 600
    ): int
    {
        $diff = $guessTime->getTimestamp() - $dailyWordTime->getTimestamp();
        $t = max(0, min($diff, $maxTime));
        $points = $maxPoints * (1 - log($t + 1) / log($maxTime + 1));

        return (int) round(max(0, $points));
    }
}