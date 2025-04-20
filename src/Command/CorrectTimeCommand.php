<?php

namespace App\Command;

use App\Entity\DailyWord;
use App\Entity\Word;
use App\Repository\DailyWordRepository;
use App\Repository\WordRepository;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
