<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\StatsRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StatsController extends AbstractController
{
    public function __construct(private readonly StatsRepositoryInterface $stats)
    {
    }

    #[Route('/stats', name: 'app_stats')]
    public function index(): Response
    {
        $byDay = $this->stats->getVotesByDay(30);

        return $this->render('stats/index.html.twig', [
            'byYear'  => $this->stats->getVotesByYear(),
            'byMonth' => $this->stats->getVotesByMonth(24),
            'byDay'   => $byDay,
            'top' => $this->stats->getTopYettisByScore(10),
            'chartLabels'   => array_column($byDay, 'period'),
            'chartPositive' => array_column($byDay, 'positive'),
            'chartNegative' => array_column($byDay, 'negative'),
        ]);
    }
}
