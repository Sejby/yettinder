<?php

declare(strict_types=1);

namespace App\Repository;

interface VoteRepositoryInterface
{
    /** @return int[] */
    public function findVotedYettiIds(string $sessionId): array;

    public function save(int $yettiId, string $sessionId, int $vote): void;
}
