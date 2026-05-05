<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\YettiInput;
use App\Form\YettiInputType;
use App\Repository\YettiRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class YettiController extends AbstractController
{
    public function __construct(
        private readonly YettiRepositoryInterface $repository,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'yettis' => $this->repository->findTopRated(),
        ]);
    }

    #[Route('/yetti/new', name: 'app_yetti_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $form = $this->createForm(YettiInputType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $input = $form->getData();
            $this->repository->save($input->toYetti());
            $this->addFlash('success', 'Yetti byl úspěšně přidán.');

            return $this->redirectToRoute('app_yetti_new');
        }

        return $this->render('yetti/new.html.twig', [
            'form' => $form,
            'yettis' => $this->repository->findRecent(5),
        ]);
    }
}
