<?php

declare(strict_types=1);

namespace App\Repository;

use App\Dto\YettiMatch;
use App\Entity\Yetti;

interface YettiRepositoryInterface
{
    /** @return list<Yetti> */
    public function findTopRated(int $limit = 10): array;

    /** @return list<Yetti> */
    public function findRecent(int $limit = 5): array;

    /** @param int[] $excludeIds */
    public function findNextMatch(array $excludeIds): ?YettiMatch;

    public function exists(int $id): bool;

    public function save(Yetti $yetti): void;
}
