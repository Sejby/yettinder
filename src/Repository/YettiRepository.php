<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\YettiMatch;
use App\Entity\Yetti;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;

final readonly class YettiRepository implements YettiRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return list<Yetti>
     * @throws Exception
     */
    public function findTopRated(int $limit = 10): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, name, gender, height, weight, address, rating FROM yetti ORDER BY rating DESC LIMIT :limit',
            ['limit' => $limit],
            ['limit' => ParameterType::INTEGER],
        );

        return array_map($this->hydrate(...), $rows);
    }

    /**
     * @return list<Yetti>
     * @throws Exception
     */
    public function findRecent(int $limit = 5): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, name, gender, height, weight, address, rating FROM yetti ORDER BY id DESC LIMIT :limit',
            ['limit' => $limit],
            ['limit' => ParameterType::INTEGER],
        );

        return array_map($this->hydrate(...), $rows);
    }

    /**
     * @param int[] $excludeIds
     * @throws Exception
     */
    public function findNextMatch(array $excludeIds): ?YettiMatch
    {
        $where = $excludeIds !== []
            ? 'WHERE y.id NOT IN (' . implode(',', array_map(intval(...), $excludeIds)) . ')'
            : '';

        $row = $this->connection->fetchAssociative(
            "SELECT y.id, y.name, y.gender, y.height, y.weight, y.address, y.rating,
                    COALESCE(SUM(v.vote), 0)  AS vote_score,
                    COUNT(v.id)               AS vote_count
             FROM yetti y
             LEFT JOIN yetti_vote v ON v.yetti_id = y.id
             $where
             GROUP BY y.id
             ORDER BY (y.rating * 2 + COALESCE(SUM(v.vote), 0) + (ABS(RANDOM()) % 6)) DESC
             LIMIT 1",
        );

        if ($row === false) {
            return null;
        }

        return new YettiMatch(
            yetti: $this->hydrate($row),
            voteScore: (int) $row['vote_score'],
            voteCount: (int) $row['vote_count'],
        );
    }

    /** @throws Exception */
    public function save(Yetti $yetti): void
    {
        $this->connection->insert('yetti', [
            'name'    => $yetti->getName(),
            'gender'  => $yetti->getGender(),
            'height'  => $yetti->getHeight(),
            'weight'  => $yetti->getWeight(),
            'address' => $yetti->getAddress(),
            'rating'  => $yetti->getRating(),
        ]);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Yetti
    {
        return new Yetti(
            id:      (int)   $row['id'],
            name:    (string) $row['name'],
            gender:  (string) $row['gender'],
            height:  (int)   $row['height'],
            weight:  (float) $row['weight'],
            address: (string) $row['address'],
            rating:  (float) $row['rating'],
        );
    }
}
