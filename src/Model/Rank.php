<?php

namespace App\Model;
use Symfony\Component\Uid\Uuid;

final readonly class Rank
{
    public function __construct(
        private Uuid $id,
        private string $username,
        private int $points,
        private string $time
    )
    {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getTime(): string
    {
        return $this->time;
    }
}