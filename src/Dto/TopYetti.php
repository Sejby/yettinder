<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class TopYetti
{
    public function __construct(
        public int    $id,
        public string $name,
        public string $address,
        public int    $voteCount,
        public int    $score,
    )
    {
    }
}
