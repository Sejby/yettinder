<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\YettiForm;
use App\Dto\YettiMatch;
use App\Entity\Yetti;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Kenny1911\DoctrineDbalHydrator\Hydrator;

final readonly class YettiRepository implements YettiRepositoryInterface
{
    public function __construct(
        private Connection $connection,
        private Hydrator   $hydrator,
    )
    {
    }

    /**
     * @return list<Yetti>
     * @throws Exception
     */
    public function findAll(): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select('id', 'name', 'gender', 'height', 'weight', 'address', 'rating')
            ->from('yetti')
            ->orderBy('name')
            ->fetchAllAssociative();

        return array_values(array_map(fn($row) => $this->hydrator->hydrate(Yetti::class, $row), $rows));
    }

    /**
     * @return list<Yetti>
     * @throws Exception
     */
    public function findTopRated(int $limit = 10): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select('id', 'name', 'gender', 'height', 'weight', 'address', 'rating')
            ->from('yetti')
            ->orderBy('rating', 'DESC')
            ->setMaxResults($limit)
            ->fetchAllAssociative();

        return array_values(array_map(fn($row) => $this->hydrator->hydrate(Yetti::class, $row), $rows));
    }

    /**
     * @return list<Yetti>
     * @throws Exception
     */
    public function findRecent(int $limit = 5): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select('id', 'name', 'gender', 'height', 'weight', 'address', 'rating')
            ->from('yetti')
            ->orderBy('id', 'DESC')
            ->setMaxResults($limit)
            ->fetchAllAssociative();

        return array_values(array_map(fn($row) => $this->hydrator->hydrate(Yetti::class, $row), $rows));
    }

    /**
     * @param int[] $excludeIds
     * @throws Exception
     */
    public function findNextMatch(array $excludeIds): ?YettiMatch
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'y.id', 'y.name', 'y.gender', 'y.height', 'y.weight', 'y.address', 'y.rating',
                'COALESCE(SUM(v.vote), 0) AS vote_score',
                'COUNT(v.id) AS vote_count',
            )
            ->from('yetti', 'y')
            ->leftJoin('y', 'yetti_vote', 'v', 'v.yetti_id = y.id')
            ->groupBy('y.id')
            ->orderBy('(y.rating * 2 + COALESCE(SUM(v.vote), 0) + (ABS(RANDOM()) % 6))', 'DESC')
            ->setMaxResults(1);

        if ($excludeIds !== []) {
            $qb->andWhere('y.id NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds, ArrayParameterType::INTEGER);
        }

        $row = $qb->fetchAssociative();

        if ($row === false) {
            return null;
        }

        return new YettiMatch(
            yetti: $this->hydrator->hydrate(Yetti::class, $row),
            voteScore: (int)$row['vote_score'],
            voteCount: (int)$row['vote_count'],
        );
    }

    /** @throws Exception */
    public function exists(int $id): bool
    {
        return (bool)$this->connection->createQueryBuilder()
            ->select('1')
            ->from('yetti')
            ->where('id = :id')
            ->setParameter('id', $id, ParameterType::INTEGER)
            ->fetchOne();
    }

    /** @throws Exception */
    public function save(YettiForm $form): void
    {
        $this->connection->insert('yetti', [
            'name' => trim($form->name),
            'gender' => $form->gender,
            'height' => $form->height,
            'weight' => $form->weight,
            'address' => trim($form->address),
            'rating' => $form->rating,
        ]);
    }
}
