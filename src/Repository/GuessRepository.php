<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DailyWord;
use App\Entity\Guess;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Guess>
 */
class GuessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Guess::class);
    }

    public function save(Guess $entity, bool $flush = false): void
    {
        $this->getEntityManager()
            ->persist($entity);

        if ($flush) {
            $this->getEntityManager()
                ->flush();
        }
    }

    public function remove(Guess $entity, bool $flush = false): void
    {
        $this->getEntityManager()
            ->remove($entity);

        if ($flush) {
            $this->getEntityManager()
                ->flush();
        }
    }

    /**
     * @return array<int, Guess>
     */
    public function findPreviousGuessesByDailyWord(DailyWord $dailyWord, User $currentUser): array
    {
        $qb = $this->createQueryBuilder('guess');

        $qb->andWhere(
            $qb->expr()->eq('guess.owner', ":owner")
        )->setParameter('owner', $currentUser->getId(), 'uuid');

        $qb->andWhere(
            $qb->expr()->eq('guess.dailyWord', ":dailyWord")
        )->setParameter('dailyWord', $dailyWord->getId(), 'uuid');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<int, Guess>
     */
    public function getCorrectGuesses(DailyWord $dailyWord): array
    {
        $qb = $this->createQueryBuilder('guess');

        $qb->andWhere(
            $qb->expr()->eq('guess.dailyWord', ":dailyWord")
        )->setParameter('dailyWord', $dailyWord->getId(), 'uuid');

        $qb->andWhere(
            $qb->expr()->eq('guess.isCorrect', ":true")
        )->setParameter('true', true);

        $qb->orderBy('guess.createdAt', Order::Ascending->value);

        return $qb->getQuery()->getResult();
    }
}
