<?php

namespace App\Command;

use App\Entity\DailyWord;
use App\Entity\Word;
use App\Repository\DailyWordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:select-daily-word')]
class SelectDailyWordCommand extends Command
{
    public function __construct(
        private DailyWordRepository $dailyWordRepository,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $wordList = ['APPLE', 'BRAIN', 'CHAIR', 'DREAM', 'EAGLE'];

        do {
            $randomWord = $wordList[array_rand($wordList)];
            $wordPreviouslyBeenUsed = $this->dailyWordRepository->findOneByWord($randomWord);
        } while ($wordPreviouslyBeenUsed);

        $word = new Word(content: $randomWord, hint: 'a useful hint');

        $dailyWord = new DailyWord();
        $dailyWord->setWord($word);

        $this->dailyWordRepository->save(entity: $dailyWord, flush: true);
        $output->writeln('New daily word selected: ' . $randomWord);

        return Command::SUCCESS;
    }

}
