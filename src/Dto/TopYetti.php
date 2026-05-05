<?php

declare(strict_types=1);

namespace App\Dto;

use Kenny1911\DoctrineDbalHydrator\Mapping\Attribute\Column;

final readonly class TopYetti
{
    public function __construct(
        #[Column] public int                     $id,
        #[Column] public string                  $name,
        #[Column] public string                  $address,
        #[Column(name: 'vote_count')] public int $voteCount,
        #[Column] public int                     $score,
    )
    {
    }
}
