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
    name: 'generate-daily-words',
    description: 'Select and schedule daily words (with hints) for the next N days.'
)]
class GenerateDailyWordCommand extends Command
{
    private const OPENAI_URL = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        private DailyWordRepository    $dailyWordRepository,
        private WordRepository         $wordRepository,
        private EntityManagerInterface $em,
        private HttpClientInterface    $httpClient,
        private ParameterBagInterface  $parameterBag,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('days', InputArgument::OPTIONAL, 'How many days ahead to schedule words for', 1)
            ->addOption('min-length', null, InputOption::VALUE_OPTIONAL, 'Minimum word length', 4)
            ->addOption('max-length', null, InputOption::VALUE_OPTIONAL, 'Maximum word length', 8);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = (int)$input->getArgument('days');
        $minLength = (int)$input->getOption('min-length');
        $maxLength = (int)$input->getOption('max-length');
        $today = CarbonImmutable::today()->setTimezone('GMT')->setTime(hour: 07, minute: 0);

        for ($i = 0; $i < $days; $i++) {
            $date = $today->addDays($i);
            $dailyWord = $this->dailyWordRepository->findByDate($date);

            if ($dailyWord instanceof DailyWord) {
                $output->writeln(
                    sprintf('<comment>Daily word already set for %s, skipping.</comment>', $date->format('Y-m-d'))
                );
                continue;
            }

            // Use OpenAI to generate both the word and the hint
            [$word, $hint] = $this->generateWordAndHint($minLength, $maxLength);

            if (!$word) {
                $output->writeln(
                    sprintf('<error>Failed to generate word for %s</error>', $date->format('Y-m-d'))
                );
                continue;
            }

            $wordExists = $this->wordRepository->findOneBy([
                'content' => strtolower($word),
            ]);
            if ($wordExists !== null) {
                [$word, $hint] = $this->generateWordAndHint($minLength, $maxLength);
            }

            $wordEntity = new Word(content: strtolower($word), hint: $hint);
            $dailyWord = new DailyWord();
            $dailyWord->setWord($wordEntity);
            $dailyWord->setDate($date);

            $this->em->persist($wordEntity);
            $this->em->persist($dailyWord);

            $output->writeln(
                "Scheduled word for {$date->format('Y-m-d')}"
            );
        }

        $this->em->flush();
        $output->writeln('<comment>Done scheduling daily words!</comment>');

        return Command::SUCCESS;
    }

    /**
     * @return string[]|null[]
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function generateWordAndHint(int $minLength, int $maxLength): array
    {
        $prompt = sprintf(
            "Generate a random uncommon English word between %d and %d letters long. Provide a tricky, indirect clue that avoids using the word's definition directly. Format the response as: word: <WORD>\nhint: <HINT>.",
            $minLength,
            $maxLength
        );

        $payload = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a creative puzzle master who makes challenging word-guessing clues.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => 80,
        ];

        $resp = $this->httpClient->request('POST', self::OPENAI_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->parameterBag->get('open-ai-key'),
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $data = $resp->toArray(false);
        $content = trim($data['choices'][0]['message']['content'] ?? '');

        if (preg_match('/word:\s*(\w+)\s*hint:\s*(.+)/i', $content, $matches)) {
            return [$matches[1], trim($matches[2])];
        }

        return [null, null];
    }
}
