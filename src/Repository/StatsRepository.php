<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\TopYetti;
use App\Dto\VoteDayStats;
use App\Dto\VotePeriodStats;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Kenny1911\DoctrineDbalHydrator\Hydrator;

final readonly class StatsRepository implements StatsRepositoryInterface
{
    public function __construct(
        private Connection $connection,
        private Hydrator   $hydrator,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function getVotesByYear(): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select(
                "strftime('%Y', voted_at) AS period",
                'COUNT(*) AS total',
                'SUM(CASE WHEN vote = 1 THEN 1 ELSE 0 END) AS positive',
                'SUM(CASE WHEN vote = -1 THEN 1 ELSE 0 END) AS negative',
                'SUM(vote) AS score',
            )
            ->from('yetti_vote')
            ->groupBy('period')
            ->orderBy('period', 'DESC')
            ->fetchAllAssociative();

        return array_values(array_map(fn($row) => $this->hydrator->hydrate(VotePeriodStats::class, $row), $rows));
    }

    /**
     * @throws Exception
     */
    public function getVotesByMonth(int $limit = 24): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select(
                "strftime('%Y-%m', voted_at) AS period",
                'COUNT(*) AS total',
                'SUM(CASE WHEN vote = 1 THEN 1 ELSE 0 END) AS positive',
                'SUM(CASE WHEN vote = -1 THEN 1 ELSE 0 END) AS negative',
                'SUM(vote) AS score',
            )
            ->from('yetti_vote')
            ->groupBy('period')
            ->orderBy('period', 'DESC')
            ->setMaxResults($limit)
            ->fetchAllAssociative();

        return array_values(array_map(fn($row) => $this->hydrator->hydrate(VotePeriodStats::class, $row), $rows));
    }

    /**
     * @throws Exception
     */
    public function getVotesByDay(int $days = 30): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select(
                "strftime('%Y-%m-%d', voted_at) AS period",
                'SUM(CASE WHEN vote = 1 THEN 1 ELSE 0 END) AS positive',
                'SUM(CASE WHEN vote = -1 THEN 1 ELSE 0 END) AS negative',
            )
            ->from('yetti_vote')
            ->where("voted_at >= date('now', :offset)")
            ->setParameter('offset', sprintf('-%d days', $days - 1))
            ->groupBy('period')
            ->orderBy('period')
            ->fetchAllAssociative();

        return $this->fillMissingDays(array_values($rows), $days);
    }

    /**
     * @throws Exception
     */
    public function getTopYettisByScore(int $limit = 10): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select(
                'y.id AS id',
                'y.name AS name',
                'y.address AS address',
                'COUNT(v.id) AS vote_count',
                'COALESCE(SUM(v.vote), 0) AS score',
            )
            ->from('yetti', 'y')
            ->leftJoin('y', 'yetti_vote', 'v', 'v.yetti_id = y.id')
            ->groupBy('y.id')
            ->orderBy('score', 'DESC')
            ->addOrderBy('vote_count', 'DESC')
            ->setMaxResults($limit)
            ->fetchAllAssociative();

        return array_values(array_map(fn($row) => $this->hydrator->hydrate(TopYetti::class, $row), $rows));
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<VoteDayStats>
     */
    private function fillMissingDays(array $rows, int $days): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['period']] = $row;
        }

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = new \DateTimeImmutable("$i days ago")->format('Y-m-d');
            $result[] = new VoteDayStats(
                period: $date,
                positive: (int)($indexed[$date]['positive'] ?? 0),
                negative: (int)($indexed[$date]['negative'] ?? 0),
            );
        }

        return $result;
    }
}
