<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\TestCase;

abstract class IntegrationTestCase extends TestCase
{
    private static ?Connection $connection = null;

    /**
     * @throws Exception
     */
    protected function getConnection(): Connection
    {
        return self::$connection ??= $this->initConnection();
    }

    /**
     * @throws Exception
     */
    private function initConnection(): Connection
    {
        $conn = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        self::createSchema($conn);

        return $conn;
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        self::$connection?->executeStatement('DELETE FROM yetti_vote');
        self::$connection?->executeStatement('DELETE FROM yetti');
    }

    /**
     * @throws Exception
     */
    private static function createSchema(Connection $connection): void
    {
        $connection->executeStatement('CREATE TABLE yetti (
            id      INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            name    VARCHAR(100) NOT NULL,
            gender  VARCHAR(10)  NOT NULL,
            height  INTEGER      NOT NULL,
            weight  REAL         NOT NULL,
            address VARCHAR(120) NOT NULL,
            rating  REAL         NOT NULL DEFAULT 0
        )');

        $connection->executeStatement('CREATE TABLE yetti_vote (
            id         INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            yetti_id   INTEGER      NOT NULL REFERENCES yetti(id),
            session_id VARCHAR(128) NOT NULL,
            vote       INTEGER      NOT NULL,
            voted_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (yetti_id, session_id)
        )');
    }

    /**
     * @throws Exception
     */
    protected function insertYetti(
        string $name = 'Test',
        string $gender = 'male',
        int $height = 175,
        float $weight = 70.0,
        string $address = 'Praha',
        float $rating = 3.0,
    ): int {
        $conn = $this->getConnection();
        $conn->insert('yetti', compact('name', 'gender', 'height', 'weight', 'address', 'rating'));

        return (int) $conn->lastInsertId();
    }

    /**
     * @throws Exception
     */
    protected function insertVote(int $yettiId, string $sessionId, int $vote, string $votedAt = ''): void
    {
        $this->getConnection()->insert('yetti_vote', [
            'yetti_id'   => $yettiId,
            'session_id' => $sessionId,
            'vote'       => $vote,
            'voted_at'   => $votedAt !== '' ? $votedAt : (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }
}
