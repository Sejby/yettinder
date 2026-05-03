<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class VoteRepository implements VoteRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @throws Exception
     */
    public function findVotedYettiIds(string $sessionId): array
    {
        $rows = $this->connection->fetchFirstColumn(
            'SELECT yetti_id FROM yetti_vote WHERE session_id = ?',
            [$sessionId],
        );

        return array_map(intval(...), $rows);
    }

    /**
     * @throws Exception
     */
    public function save(int $yettiId, string $sessionId, int $vote): void
    {
        $this->connection->executeStatement(
            'INSERT OR IGNORE INTO yetti_vote (yetti_id, session_id, vote) VALUES (?, ?, ?)',
            [$yettiId, $sessionId, $vote],
        );
    }
}
