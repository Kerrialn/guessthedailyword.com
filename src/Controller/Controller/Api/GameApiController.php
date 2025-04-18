<?php

namespace App\Controller\Controller\Api;

use App\Entity\Guess;
use App\Repository\DailyWordRepository;
use App\Repository\GuessRepository;
use App\Service\GameHelperService;
use App\Service\PointsCalculatorService;
use Carbon\CarbonImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api')]
class GameApiController extends AbstractController
{

    public function __construct(
        private readonly DailyWordRepository     $dailyWordRepository,
        private readonly GameHelperService       $gameHelperService,
        private readonly GuessRepository         $guessRepository,
        private readonly PointsCalculatorService $pointsCalculatorService
    )
    {
    }

    #[Route(path: '/guess', name: 'guess')]
    public function create(Request $request): JsonResponse
    {
        $guess = json_decode($request->getContent());
        $currentUser = $this->getUser();
        $dailyWord = $this->dailyWordRepository->getDailyWord();
        $isCorrect = $this->gameHelperService->checkGuess(guessWord: $guess->content, dailyWord: $dailyWord->getWord()->getContent());
        $points = $this->pointsCalculatorService->calculatePoints(guessTime: CarbonImmutable::now(), dailyWordTime: $dailyWord->getCreatedAt(), maxPoints: 1000000);

        $guess = new Guess(
            owner: $currentUser,
            dailyWord: $dailyWord,
            content: $guess->content,
            isCorrect: $isCorrect,
            points: $isCorrect ? $points : null
        );

        $this->guessRepository->save(entity: $guess, flush: true);
        return $this->json(['isCorrect' => $guess->getIsCorrect()]);
    }

}