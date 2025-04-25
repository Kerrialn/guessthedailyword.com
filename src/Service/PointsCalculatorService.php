<?php

namespace App\Service;

use Carbon\CarbonImmutable;

final readonly class PointsCalculatorService
{
    public function calculatePoints(
        CarbonImmutable $guessTime,
        CarbonImmutable $dailyWordTime,
        int $maxPoints = 100
    ): int
    {
        $maxTime = 86399;
        $diff = abs($guessTime->getTimestamp() - $dailyWordTime->getTimestamp());
        $points = $maxPoints * pow(1 - $diff / $maxTime, 2);
        return (int) round(max(1, $points));
    }
}