<?php

declare(strict_types=1);

namespace App\Matching;

use App\Dto\YettiMatch;
use App\Repository\VoteRepositoryInterface;
use App\Repository\YettiRepositoryInterface;

final readonly class MatchingService
{
    public function __construct(
        private YettiRepositoryInterface $yettiRepository,
        private VoteRepositoryInterface  $voteRepository,
    ) {
    }

    public function findMatch(string $sessionId): ?YettiMatch
    {
        $votedIds = $this->voteRepository->findVotedYettiIds($sessionId);

        return $this->yettiRepository->findNextMatch($votedIds);
    }

    public function vote(int $yettiId, string $sessionId, int $vote): void
    {
        $this->voteRepository->save($yettiId, $sessionId, $vote);
    }
}
