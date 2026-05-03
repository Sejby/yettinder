<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Yetti;
use App\Repository\YettiRepository;
use App\Tests\Integration\IntegrationTestCase;
use Doctrine\DBAL\Exception;

final class YettiRepositoryTest extends IntegrationTestCase
{
    private YettiRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new YettiRepository($this->getConnection());
    }

    /**
     * @throws Exception
     */
    public function testSavePersistsYetti(): void
    {
        $yetti = new Yetti(null, 'Mira', 'female', 165, 58.0, 'Brno', 4.5);
        $this->repository->save($yetti);

        $results = $this->repository->findTopRated(10);

        $this->assertCount(1, $results);
        $this->assertSame('Mira', $results[0]->getName());
        $this->assertSame(165, $results[0]->getHeight());
    }

    /**
     * @throws Exception
     */
    public function testFindTopRatedOrdersByRatingDescending(): void
    {
        $this->insertYetti(name: 'Low',  rating: 1.0);
        $this->insertYetti(name: 'High', rating: 5.0);
        $this->insertYetti(name: 'Mid',  rating: 3.0);

        $results = $this->repository->findTopRated(10);

        $this->assertSame('High', $results[0]->getName());
        $this->assertSame('Mid',  $results[1]->getName());
        $this->assertSame('Low',  $results[2]->getName());
    }

    /**
     * @throws Exception
     */
    public function testFindTopRatedRespectsLimit(): void
    {
        $this->insertYetti(name: 'A', rating: 5.0);
        $this->insertYetti(name: 'B', rating: 4.0);
        $this->insertYetti(name: 'C', rating: 3.0);

        $this->assertCount(2, $this->repository->findTopRated(2));
    }

    /**
     * @throws Exception
     */
    public function testFindRecentOrdersByIdDescending(): void
    {
        $this->insertYetti(name: 'First');
        $this->insertYetti(name: 'Second');
        $this->insertYetti(name: 'Third');

        $results = $this->repository->findRecent(3);

        $this->assertSame('Third',  $results[0]->getName());
        $this->assertSame('Second', $results[1]->getName());
        $this->assertSame('First',  $results[2]->getName());
    }

    /**
     * @throws Exception
     */
    public function testFindNextMatchReturnsNullWhenTableEmpty(): void
    {
        $this->assertNull($this->repository->findNextMatch([]));
    }

    /**
     * @throws Exception
     */
    public function testFindNextMatchReturnsNullWhenAllExcluded(): void
    {
        $id = $this->insertYetti();

        $this->assertNull($this->repository->findNextMatch([$id]));
    }

    /**
     * @throws Exception
     */
    public function testFindNextMatchExcludesGivenIds(): void
    {
        $idA = $this->insertYetti(name: 'A', rating: 5.0);
        $idB = $this->insertYetti(name: 'B', rating: 4.0);

        $match = $this->repository->findNextMatch([$idA]);

        $this->assertNotNull($match);
        $this->assertSame('B', $match->yetti->getName());
    }

    /**
     * @throws Exception
     */
    public function testFindNextMatchReturnsVoteScoreAndCount(): void
    {
        $id = $this->insertYetti();
        $this->insertVote($id, 'session1', 1);
        $this->insertVote($id, 'session2', 1);
        $this->insertVote($id, 'session3', -1);

        $match = $this->repository->findNextMatch([]);

        $this->assertNotNull($match);
        $this->assertSame(1, $match->voteScore);
        $this->assertSame(3, $match->voteCount);
    }
}
