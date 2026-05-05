<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\VoteRepository;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\DBAL\Exception;

final class VoteRepositoryTest extends IntegrationTestCase
{
    private VoteRepository $repository;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new VoteRepository($this->getConnection());
    }

    /**
     * @throws Exception
     */
    public function testSavePersistsVote(): void
    {
        $yettiId = $this->insertYetti();
        $this->repository->save($yettiId, 'session-1', 1);

        $ids = $this->repository->findVotedYettiIds('session-1');

        $this->assertSame([$yettiId], $ids);
    }

    /**
     * @throws Exception
     */
    public function testFindVotedYettiIdsReturnsOnlyIdsForGivenSession(): void
    {
        $idA = $this->insertYetti(name: 'A');
        $idB = $this->insertYetti(name: 'B');
        $idC = $this->insertYetti(name: 'C');

        $this->repository->save($idA, 'session-1', 1);
        $this->repository->save($idB, 'session-1', -1);
        $this->repository->save($idC, 'session-2', 1);

        $session1Ids = $this->repository->findVotedYettiIds('session-1');

        $this->assertCount(2, $session1Ids);
        $this->assertContains($idA, $session1Ids);
        $this->assertContains($idB, $session1Ids);
        $this->assertNotContains($idC, $session1Ids);
    }

    /**
     * @throws Exception
     */
    public function testFindVotedYettiIdsReturnsEmptyArrayForUnknownSession(): void
    {
        $this->assertSame([], $this->repository->findVotedYettiIds('nonexistent-session'));
    }

    /**
     * @throws Exception
     */
    public function testDoubleVoteIsIgnored(): void
    {
        $yettiId = $this->insertYetti();

        $this->repository->save($yettiId, 'session-1', 1);
        $this->repository->save($yettiId, 'session-1', -1);

        $count = $this->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM yetti_vote WHERE yetti_id = ? AND session_id = ?',
            [$yettiId, 'session-1'],
        );

        $this->assertSame(1, (int)$count);
    }
}
