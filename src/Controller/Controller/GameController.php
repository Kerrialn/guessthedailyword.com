<?php

namespace App\Controller\Controller;

use App\Entity\Guess;
use App\Entity\User;
use App\Repository\DailyWordRepository;
use App\Repository\GuessRepository;
use App\Security\Voter\GameVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GameController extends AbstractController
{

    public function __construct(
        private readonly DailyWordRepository $dailyWordRepository,
        private readonly GuessRepository     $guessRepository
    )
    {
    }

    #[Route(path: '/', name: 'landing')]
    public function index(#[CurrentUser] User $currentUser): Response
    {
        $dailyWord = $this->dailyWordRepository->getDailyWord();

        $canUserGuess = $this->isGranted(GameVoter::CAN_GUESS, $dailyWord);
        if (!$canUserGuess) {
            $userGuesses = $dailyWord->getGuesses()->filter(
                fn(Guess $guess) => $guess->getOwner() === $currentUser
            );

            $hasCorrectGuess = $userGuesses->findFirst(
                fn(int $key, Guess $guess) => $guess->getIsCorrect()
            );

            if($hasCorrectGuess){
                return $this->redirectToRoute('leaderboard', ['_fragment' => $currentUser->getId()]);
            }

            return $this->redirectToRoute('game_over');
        }

        $previousGuesses = $this->guessRepository->findPreviousGuessesByDailyWord($dailyWord, $currentUser);
        $previousGuessesArray = array_map(function($guess) {
            return [
                'content' => $guess->getContent(),
                'isCorrect' => $guess->getIsCorrect(),
            ];
        }, $previousGuesses);

        return $this->render('game/landing.html.twig', [
            'dailyWord' => $dailyWord,
            'previousGuessesArray' => $previousGuessesArray
        ]);
    }

    #[Route(path: '/game-over', name: 'game_over')]
    public function over(): Response
    {
        return $this->render('game/game-over.html.twig');
    }

}