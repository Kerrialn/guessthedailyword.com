<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DailyWord;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DailyWord>
 */
class DailyWordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyWord::class);
    }

    public function save(DailyWord $entity, bool $flush = false): void
    {
        $this->getEntityManager()
            ->persist($entity);

        if ($flush) {
            $this->getEntityManager()
                ->flush();
        }
    }

    public function remove(DailyWord $entity, bool $flush = false): void
    {
        $this->getEntityManager()
            ->remove($entity);

        if ($flush) {
            $this->getEntityManager()
                ->flush();
        }
    }

    public function getDailyWord(): null|DailyWord
    {
        $qb = $this->createQueryBuilder('daily_word');

        $qb->andWhere(
            $qb->expr()->between('daily_word.date', ':todayStart', ':todayEnd')
        )->setParameter('todayStart', CarbonImmutable::today('GMT')->startOfDay()->toDateTimeImmutable())
            ->setParameter('todayEnd', CarbonImmutable::today('GMT')->endOfDay()->toDateTimeImmutable());

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOneByWord(string $randomWord): null|DailyWord
    {
        $qb = $this->createQueryBuilder('daily_word');
        $qb->leftJoin('daily_word.word', 'word');
        $qb->andWhere(
            $qb->expr()->eq('word.content', ':word')
        )->setParameter('word', $randomWord);

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
