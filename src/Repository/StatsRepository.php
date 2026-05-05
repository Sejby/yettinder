<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\TopYetti;
use App\Dto\VoteDayStats;
use App\Dto\VotePeriodStats;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;

final readonly class StatsRepository implements StatsRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @throws Exception
     */
    public function getVotesByYear(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT
                strftime('%Y', voted_at)                              AS period,
                COUNT(*)                                              AS total,
                SUM(CASE WHEN vote = 1  THEN 1 ELSE 0 END)           AS positive,
                SUM(CASE WHEN vote = -1 THEN 1 ELSE 0 END)           AS negative,
                SUM(vote)                                             AS score
             FROM yetti_vote
             GROUP BY period
             ORDER BY period DESC",
        );

        return array_map($this->hydratePeriod(...), $rows);
    }

    /**
     * @throws Exception
     */
    public function getVotesByMonth(int $limit = 24): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT
                strftime('%Y-%m', voted_at)                          AS period,
                COUNT(*)                                              AS total,
                SUM(CASE WHEN vote = 1  THEN 1 ELSE 0 END)           AS positive,
                SUM(CASE WHEN vote = -1 THEN 1 ELSE 0 END)           AS negative,
                SUM(vote)                                             AS score
             FROM yetti_vote
             GROUP BY period
             ORDER BY period DESC
             LIMIT :limit",
            ['limit' => $limit],
            ['limit' => ParameterType::INTEGER],
        );

        return array_map($this->hydratePeriod(...), $rows);
    }

    /**
     * @throws Exception
     */
    public function getVotesByDay(int $days = 30): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT
                strftime('%Y-%m-%d', voted_at)                       AS period,
                SUM(CASE WHEN vote = 1  THEN 1 ELSE 0 END)           AS positive,
                SUM(CASE WHEN vote = -1 THEN 1 ELSE 0 END)           AS negative
             FROM yetti_vote
             WHERE voted_at >= date('now', :offset)
             GROUP BY period
             ORDER BY period",
            ['offset' => sprintf('-%d days', $days - 1)],
        );

        return $this->fillMissingDays($rows, $days);
    }

    /**
     * @throws Exception
     */
    public function getTopYettisByScore(int $limit = 10): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT
                y.id                                                  AS id,
                y.name                                                AS name,
                y.address                                             AS address,
                COUNT(v.id)                                           AS vote_count,
                COALESCE(SUM(v.vote), 0)                              AS score
             FROM yetti y
             LEFT JOIN yetti_vote v ON v.yetti_id = y.id
             GROUP BY y.id
             ORDER BY score DESC, vote_count DESC
             LIMIT :limit",
            ['limit' => $limit],
            ['limit' => ParameterType::INTEGER],
        );

        return array_map(static fn(array $r) => new TopYetti(
            id: (int)$r['id'],
            name: (string)$r['name'],
            address: (string)$r['address'],
            voteCount: (int)$r['vote_count'],
            score: (int)$r['score'],
        ), $rows);
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

    /** @param array<string, mixed> $row */
    private function hydratePeriod(array $row): VotePeriodStats
    {
        return new VotePeriodStats(
            period: (string)$row['period'],
            total: (int)$row['total'],
            positive: (int)$row['positive'],
            negative: (int)$row['negative'],
            score: (int)$row['score'],
        );
    }
}
