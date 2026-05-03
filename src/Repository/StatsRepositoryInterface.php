<?php

declare(strict_types=1);

namespace App\Repository;

interface StatsRepositoryInterface
{
    /** @return list<array{period: string, total: int, positive: int, negative: int, score: int}> */
    public function getVotesByYear(): array;

    /** @return list<array{period: string, total: int, positive: int, negative: int, score: int}> */
    public function getVotesByMonth(int $limit = 24): array;

    /** @return list<array{period: string, positive: int, negative: int}> */
    public function getVotesByDay(int $days = 30): array;

    /** @return list<array{id: int, name: string, address: string, vote_count: int, score: int}> */
    public function getTopYettisByScore(int $limit = 10): array;
}
