<?php

declare(strict_types=1);

namespace App\Tests\Unit\Matching;

use App\Dto\YettiMatch;
use App\Entity\Yetti;
use App\Matching\MatchingService;
use App\Repository\VoteRepositoryInterface;
use App\Repository\YettiRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class MatchingServiceTest extends TestCase
{
    private function makeMatch(): YettiMatch
    {
        $yetti = new Yetti(1, 'Test', 'male', 180, 75.0, 'Praha', 4.0);

        return new YettiMatch($yetti, 3, 5);
    }

    public function testFindMatchDelegatesToRepositoriesInOrder(): void
    {
        $votedIds = [1, 2];
        $match    = $this->makeMatch();

        $voteRepo = $this->createMock(VoteRepositoryInterface::class);
        $voteRepo->expects($this->once())
            ->method('findVotedYettiIds')
            ->with('session-abc')
            ->willReturn($votedIds);

        $yettiRepo = $this->createMock(YettiRepositoryInterface::class);
        $yettiRepo->expects($this->once())
            ->method('findNextMatch')
            ->with($votedIds)
            ->willReturn($match);

        $service = new MatchingService($yettiRepo, $voteRepo);

        $this->assertSame($match, $service->findMatch('session-abc'));
    }

    public function testFindMatchReturnsNullWhenAllRated(): void
    {
        $voteRepo = $this->createStub(VoteRepositoryInterface::class);
        $voteRepo->method('findVotedYettiIds')->willReturn([1, 2, 3]);

        $yettiRepo = $this->createStub(YettiRepositoryInterface::class);
        $yettiRepo->method('findNextMatch')->willReturn(null);

        $service = new MatchingService($yettiRepo, $voteRepo);

        $this->assertNull($service->findMatch('session-abc'));
    }

    public function testFindMatchReturnsNullWhenNoYettisExist(): void
    {
        $voteRepo = $this->createStub(VoteRepositoryInterface::class);
        $voteRepo->method('findVotedYettiIds')->willReturn([]);

        $yettiRepo = $this->createStub(YettiRepositoryInterface::class);
        $yettiRepo->method('findNextMatch')->willReturn(null);

        $service = new MatchingService($yettiRepo, $voteRepo);

        $this->assertNull($service->findMatch('session-abc'));
    }

    public function testVoteDelegatesToVoteRepository(): void
    {
        $yettiRepo = $this->createStub(YettiRepositoryInterface::class);

        $voteRepo = $this->createMock(VoteRepositoryInterface::class);
        $voteRepo->expects($this->once())
            ->method('save')
            ->with(42, 'session-abc', 1);

        $service = new MatchingService($yettiRepo, $voteRepo);
        $service->vote(42, 'session-abc', 1);
    }
}
