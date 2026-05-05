<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\StatsRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class StatsController extends AbstractController
{
    public function __construct(
        private readonly StatsRepositoryInterface $stats,
        private readonly ChartBuilderInterface $chartBuilder,
    ) {
    }

    #[Route('/stats', name: 'app_stats')]
    public function index(): Response
    {
        $byDay = $this->stats->getVotesByDay(30);

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels'   => array_column($byDay, 'period'),
            'datasets' => [
                [
                    'label'           => '👍',
                    'data'            => array_column($byDay, 'positive'),
                    'backgroundColor' => 'rgba(0, 255, 0, 1)',
                    'borderRadius'    => 10,
                ],
                [
                    'label'           => '👎',
                    'data'            => array_column($byDay, 'negative'),
                    'backgroundColor' => 'rgba(255, 0, 0, 1)',
                    'borderRadius'    => 10,
                ],
            ],
        ]);
        $chart->setOptions([
            'responsive' => true,
            'scales'     => [
                'x' => ['stacked' => false, 'grid' => ['display' => false]],
                'y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]],
            ],
            'plugins' => [
                'legend' => ['position' => 'top'],
            ],
        ]);

        return $this->render('stats/index.html.twig', [
            'byYear'  => $this->stats->getVotesByYear(),
            'byMonth' => $this->stats->getVotesByMonth(24),
            'top'     => $this->stats->getTopYettisByScore(10),
            'chart'   => $chart,
        ]);
    }
}
