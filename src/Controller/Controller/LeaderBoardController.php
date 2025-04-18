<?php

namespace App\Controller\Controller;

use App\Entity\DailyWord;
use App\Entity\User;
use App\Model\Rank;
use App\Repository\DailyWordRepository;
use App\Repository\GuessRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LeaderBoardController extends AbstractController
{


    public function __construct(
        private readonly DailyWordRepository     $dailyWordRepository,
        private readonly GuessRepository         $guessRepository
    )
    {
    }

    #[Route(path: '/leaderboard', name: 'leaderboard')]
    public function index(): Response
    {
        $dailyWord = $this->dailyWordRepository->getDailyWord();
        $correctGuesses = $this->guessRepository->getCorrectGuesses($dailyWord);

        $ranking = new ArrayCollection();
        foreach ($correctGuesses as $index => $guess) {
            $rank = new Rank(
                id: $guess->getOwner()->getId(),
                username: $guess->getOwner()->getName(),
                points: $guess->getPoints(),
                time: $guess->getCreatedAt()->diffAsCarbonInterval($guess->getDailyWord()->getCreatedAt())->forHumans(short: true)
            );
            $ranking->add($rank);
        }

        return $this->render('game/leaderboard.html.twig', [
            'dailyWord' => $dailyWord,
            'ranking' => $ranking
        ]);
    }

}