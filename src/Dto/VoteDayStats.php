<?php

declare(strict_types=1);

namespace App\Dto;

use Kenny1911\DoctrineDbalHydrator\Mapping\Attribute\Column;

final readonly class VoteDayStats
{
    public function __construct(
        #[Column] public string $period,
        #[Column] public int    $positive,
        #[Column] public int    $negative,
    )
    {
    }
}
