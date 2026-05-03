<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\StatsRepository;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\DBAL\Exception;

final class StatsRepositoryTest extends IntegrationTestCase
{
    private StatsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new StatsRepository($this->getConnection());
    }

    /**
     * @throws Exception
     */
    public function testGetVotesByYearReturnsEmptyArrayWhenNoVotes(): void
    {
        $this->assertSame([], $this->repository->getVotesByYear());
    }

    public function testGetVotesByYearGroupsAndAggregatesCorrectly(): void
    {
        $id = $this->insertYetti();
        $this->insertVote($id, 'session-a', 1,  '2025-06-01 10:00:00');
        $this->insertVote($id, 'session-b', -1, '2025-06-02 10:00:00');
        $this->insertVote($id, 'session-c', 1,  '2026-01-15 10:00:00');

        $rows = $this->repository->getVotesByYear();

        $this->assertCount(2, $rows);

        $this->assertSame('2026', $rows[0]['period']);
        $this->assertSame(1,  $rows[0]['total']);
        $this->assertSame(1,  $rows[0]['positive']);
        $this->assertSame(0,  $rows[0]['negative']);
        $this->assertSame(1,  $rows[0]['score']);

        $this->assertSame('2025', $rows[1]['period']);
        $this->assertSame(2, $rows[1]['total']);
        $this->assertSame(1, $rows[1]['positive']);
        $this->assertSame(1, $rows[1]['negative']);
        $this->assertSame(0, $rows[1]['score']);
    }

    /**
     * @throws Exception
     */
    public function testGetVotesByMonthGroupsByYearAndMonth(): void
    {
        $id = $this->insertYetti();
        $this->insertVote($id, 'session-a', 1,  '2026-03-10 10:00:00');
        $this->insertVote($id, 'session-b', 1,  '2026-04-05 10:00:00');
        $this->insertVote($id, 'session-c', -1, '2026-04-20 10:00:00');

        $rows = $this->repository->getVotesByMonth(24);

        $byPeriod = array_column($rows, null, 'period');
        $this->assertArrayHasKey('2026-03', $byPeriod);
        $this->assertArrayHasKey('2026-04', $byPeriod);

        $april = $byPeriod['2026-04'];
        $this->assertSame(2, $april['total']);
        $this->assertSame(1, $april['positive']);
        $this->assertSame(1, $april['negative']);
        $this->assertSame(0, $april['score']);
    }

    /**
     * @throws Exception
     */
    public function testGetVotesByMonthRespectsLimit(): void
    {
        $id = $this->insertYetti();
        $this->insertVote($id, 'session-a', 1, '2024-01-01 10:00:00');
        $this->insertVote($id, 'session-b', 1, '2025-01-01 10:00:00');
        $this->insertVote($id, 'session-c', 1, '2026-01-01 10:00:00');

        $this->assertCount(1, $this->repository->getVotesByMonth(1));
    }

    /**
     * @throws Exception
     */
    public function testGetVotesByDayAlwaysReturnsRequestedNumberOfDays(): void
    {
        $rows = $this->repository->getVotesByDay(30);

        $this->assertCount(30, $rows);
    }

    public function testGetVotesByDayFillsMissingDaysWithZeros(): void
    {
        $id = $this->insertYetti();
        $today = new \DateTimeImmutable()->format('Y-m-d H:i:s');
        $this->insertVote($id, 'session-a', 1, $today);

        $rows     = $this->repository->getVotesByDay(30);
        $byPeriod = array_column($rows, null, 'period');

        $todayKey = new \DateTimeImmutable()->format('Y-m-d');
        $this->assertSame(1, $byPeriod[$todayKey]['positive']);
        $this->assertSame(0, $byPeriod[$todayKey]['negative']);

        $zeroDays = array_filter($rows, static fn(array $r) => $r['positive'] === 0 && $r['negative'] === 0);
        $this->assertCount(29, $zeroDays);
    }

    public function testGetVotesByDayAggregatesPositiveAndNegativeSeparately(): void
    {
        $id    = $this->insertYetti();
        $today = new \DateTimeImmutable()->format('Y-m-d H:i:s');
        $this->insertVote($id, 'session-a', 1,  $today);
        $this->insertVote($id, 'session-b', 1,  $today);
        $this->insertVote($id, 'session-c', -1, $today);

        $rows = $this->repository->getVotesByDay(7);

        $todayKey = new \DateTimeImmutable()->format('Y-m-d');
        $todayRow = array_values(array_filter($rows, static fn(array $r) => $r['period'] === $todayKey))[0];

        $this->assertSame(2, $todayRow['positive']);
        $this->assertSame(1, $todayRow['negative']);
    }

    public function testGetTopYettisByScoreOrdersByScoreDescending(): void
    {
        $idA = $this->insertYetti(name: 'Popular');
        $idB = $this->insertYetti(name: 'Disliked');
        $idC = $this->insertYetti(name: 'Neutral');

        $this->insertVote($idA, 'session-1', 1);
        $this->insertVote($idA, 'session-2', 1);
        $this->insertVote($idB, 'session-1', -1);

        $rows = $this->repository->getTopYettisByScore(10);

        $this->assertSame('Popular',  $rows[0]['name']);
        $this->assertSame(2,          $rows[0]['score']);
        $this->assertSame(2,          $rows[0]['vote_count']);
    }

    /**
     * @throws Exception
     */
    public function testGetTopYettisByScoreIncludesYettisWithNoVotes(): void
    {
        $this->insertYetti(name: 'NoVotes');

        $rows = $this->repository->getTopYettisByScore(10);

        $this->assertCount(1, $rows);
        $this->assertSame(0, $rows[0]['score']);
        $this->assertSame(0, $rows[0]['vote_count']);
    }

    public function testGetTopYettisByScoreRespectsLimit(): void
    {
        $this->insertYetti(name: 'A');
        $this->insertYetti(name: 'B');
        $this->insertYetti(name: 'C');

        $this->assertCount(2, $this->repository->getTopYettisByScore(2));
    }
}
