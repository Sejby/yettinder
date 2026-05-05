<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Yetti;

final readonly class YettiMatch
{
    public function __construct(
        public Yetti $yetti,
        public int   $voteScore,
        public int   $voteCount,
    )
    {
    }
}
