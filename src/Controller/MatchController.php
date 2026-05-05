<?php

declare(strict_types=1);

namespace App\Controller;

use App\Matching\MatchingService;
use App\Repository\YettiRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MatchController extends AbstractController
{
    public function __construct(
        private readonly MatchingService          $matchingService,
        private readonly YettiRepositoryInterface $yettiRepository,
    )
    {
    }

    #[Route('/match', name: 'app_match')]
    public function index(Request $request): Response
    {
        $sessionId = $request->getSession()->getId();
        $match = $this->matchingService->findMatch($sessionId);

        return $this->render('match/index.html.twig', [
            'match' => $match,
            'allRated' => $match === null && $this->yettiRepository->findTopRated(1) !== [],
        ]);
    }

    #[Route('/match/{id}/vote', name: 'app_match_vote', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function vote(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('vote_yetti', (string)$request->request->get('_token'))) {
            throw new AccessDeniedHttpException('Neplatný bezpečnostní token.');
        }

        if (!$this->yettiRepository->exists($id)) {
            throw $this->createNotFoundException('Yetti nenalezen.');
        }

        $vote = (int)$request->request->get('vote');
        if (!in_array($vote, [-1, 1], true)) {
            throw $this->createNotFoundException('Neplatná hodnota hlasu.');
        }

        $this->matchingService->vote($id, $request->getSession()->getId(), $vote);

        return $this->redirectToRoute('app_match');
    }
}
