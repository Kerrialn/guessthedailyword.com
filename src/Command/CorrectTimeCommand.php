<?php

namespace App\Command;

use App\Repository\DailyWordRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'fix-daily-words-time',
    description: 'fixes the times of the daily word'
)]
class CorrectTimeCommand extends Command
{
    public function __construct(
        private DailyWordRepository $dailyWordRepository,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dailyWords = $this->dailyWordRepository->findAll();
        foreach ($dailyWords as $dailyWord) {
            $newDate = $dailyWord->getDate()->setTimezone('GMT')->setTime(hour: 07, minute: 0);
            $output->writeln("<comment>fixing old-date: {$dailyWord->getDate()->toAtomString()} to {$newDate->toAtomString()}</comment>");
            $dailyWord->setDate($newDate);
            $this->dailyWordRepository->save(entity: $dailyWord, flush: true);
        }

        return Command::SUCCESS;
    }
}
