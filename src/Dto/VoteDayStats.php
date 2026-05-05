<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class VoteDayStats
{
    public function __construct(
        public string $period,
        public int    $positive,
        public int    $negative,
    )
    {
    }
}
