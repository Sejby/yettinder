<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class VotePeriodStats
{
    public function __construct(
        public string $period,
        public int    $total,
        public int    $positive,
        public int    $negative,
        public int    $score,
    )
    {
    }
}
