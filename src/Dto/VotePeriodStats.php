<?php

declare(strict_types=1);

namespace App\Dto;

use Kenny1911\DoctrineDbalHydrator\Mapping\Attribute\Column;

final readonly class VotePeriodStats
{
    public function __construct(
        #[Column] public string $period,
        #[Column] public int    $total,
        #[Column] public int    $positive,
        #[Column] public int    $negative,
        #[Column] public int    $score,
    )
    {
    }
}
