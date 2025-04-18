<?php

namespace App\Entity;

use App\Repository\GuessRepository;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuessRepository::class)]
class Guess
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'owner')]
    private null|User $owner = null;

    #[ORM\ManyToOne(targetEntity: DailyWord::class, inversedBy: 'guesses')]
    private null|DailyWord $dailyWord = null;

    #[ORM\Column(length: 255)]
    private null|string $content = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private null|int $points;
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isCorrect;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private CarbonImmutable $createdAt;

    /**
     * @param User|null $owner
     * @param DailyWord|null $dailyWord
     * @param string|null $content
     */
    public function __construct(
        ?User $owner,
        ?DailyWord $dailyWord,
        ?string $content,
        ?bool $isCorrect,
        null|int $points = null
    )
    {
        $this->owner = $owner;
        $this->dailyWord = $dailyWord;
        $this->content = $content;
        $this->points = $points;
        $this->isCorrect = $isCorrect;
        $this->createdAt = CarbonImmutable::now();
    }


    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    public function getDailyWord(): ?DailyWord
    {
        return $this->dailyWord;
    }

    public function setDailyWord(?DailyWord $dailyWord): void
    {
        $this->dailyWord = $dailyWord;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getCreatedAt(): CarbonImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface|CarbonImmutable $createdAt): void
    {
        $this->createdAt = CarbonImmutable::parse($createdAt);
    }

    public function getIsCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): void
    {
        $this->isCorrect = $isCorrect;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): void
    {
        $this->points = $points;
    }

}