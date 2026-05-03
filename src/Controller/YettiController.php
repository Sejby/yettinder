<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\YettiInput;
use App\Repository\YettiRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class YettiController extends AbstractController
{
    public function __construct(
        private readonly YettiRepositoryInterface $repository,
        private readonly ValidatorInterface $validator,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'yettis' => $this->repository->findTopRated(10),
        ]);
    }

    #[Route('/yetti/new', name: 'app_yetti_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $errors = [];
        $input = new YettiInput();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('create_yetti', (string) $request->request->get('_token'))) {
                $errors[] = 'Neplatný bezpečnostní token. Zkuste prosím znovu.';
            } else {
                /** @var YettiInput $input */
                $input = $this->denormalizer->denormalize($request->request->all(), YettiInput::class);

                $violations = $this->validator->validate($input);

                if (count($violations) === 0) {
                    $this->repository->save($input->toYetti());
                    $this->addFlash('success', 'Yetti byl úspěšně přidán.');

                    return $this->redirectToRoute('app_yetti_new');
                }

                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }
            }
        }

        return $this->render('yetti/new.html.twig', [
            'form' => $input,
            'errors' => $errors,
            'yettis' => $this->repository->findRecent(5),
        ]);
    }
}
