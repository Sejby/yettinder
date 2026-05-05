<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\TopYetti;
use App\Dto\VoteDayStats;
use App\Dto\VotePeriodStats;

interface StatsRepositoryInterface
{
    /** @return list<VotePeriodStats> */
    public function getVotesByYear(): array;

    /** @return list<VotePeriodStats> */
    public function getVotesByMonth(int $limit = 24): array;

    /** @return list<VoteDayStats> */
    public function getVotesByDay(int $days = 30): array;

    /** @return list<TopYetti> */
    public function getTopYettisByScore(int $limit = 10): array;
}
