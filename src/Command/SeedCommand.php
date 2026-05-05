<?php

declare(strict_types=1);

namespace App\Command;

use App\Dto\YettiForm;
use App\Repository\YettiRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:db:seed', description: 'Seed the database with sample yetti data if empty')]
final class SeedCommand extends Command
{
    private const array YETTIS = [
        ['Bořivoj Sněžný', 'male', 213, 98.5, 'Krkonoše, Česká republika', 4.2],
        ['Světlana Mrazivá', 'female', 187, 74.0, 'Beskydy, Česká republika', 3.8],
        ['Oldřich Ledový', 'male', 245, 130.0, 'Šumava, Česká republika', 4.7],
        ['Božena Horská', 'female', 172, 68.5, 'Jeseníky, Česká republika', 3.1],
        ['Přemysl Vichřice', 'male', 231, 115.0, 'Krkonoše, Česká republika', 4.9],
        ['Milada Závějová', 'female', 195, 82.0, 'Orlické hory, Česká republika', 2.6],
        ['Radovan Mrazík', 'male', 260, 145.0, 'Tatry, Slovensko', 4.4],
        ['Zdenka Sněhová', 'female', 168, 63.0, 'Krušné hory, Česká republika', 3.5],
        ['Ctibor Ledovec', 'male', 278, 162.5, 'Alpy, Rakousko', 5.0],
        ['Jaroslava Zimní', 'female', 181, 77.0, 'Jizerské hory, Česká republika', 2.9],
        ['Slavomír Polární', 'male', 222, 108.0, 'Krkonoše, Česká republika', 4.1],
        ['Růžena Vánice', 'female', 176, 70.5, 'Beskydy, Česká republika', 3.3],
        ['Vladimír Blizzard', 'male', 238, 122.0, 'Vysoké Tatry, Slovensko', 4.6],
        ['Hedvika Frostová', 'female', 190, 79.0, 'Šumava, Česká republika', 3.7],
        ['Bohumil Permafrost', 'male', 255, 140.0, 'Jeseníky, Česká republika', 4.8],
    ];

    private const int VOTE_DAYS = 90;
    private const int VOTES_PER_DAY_MIN = 3;
    private const int VOTES_PER_DAY_MAX = 25;

    public function __construct(
        private readonly YettiRepositoryInterface $repository,
        private readonly Connection               $connection,
    )
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     * @throws RandomException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->repository->findTopRated(1) !== []) {
            $io->note('Database already contains yettis – skipping seed.');
            return Command::SUCCESS;
        }

        foreach (self::YETTIS as [$name, $gender, $height, $weight, $address, $rating]) {
            $form = new YettiForm();
            $form->name = $name;
            $form->gender = $gender;
            $form->height = $height;
            $form->weight = $weight;
            $form->address = $address;
            $form->rating = $rating;
            $this->repository->save($form);
        }

        $yettiIds = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('yetti')
            ->fetchFirstColumn();
        $voteCount = $this->seedVotes(array_values($yettiIds));

        $io->success(sprintf(
            'Seeded %d yettis and %d votes into the database.',
            count(self::YETTIS),
            $voteCount,
        ));

        return Command::SUCCESS;
    }

    /**
     * @param list<int> $yettiIds
     * @throws RandomException
     * @throws Exception
     */
    private function seedVotes(array $yettiIds): int
    {
        $total = 0;
        $sessionCounter = 0;

        for ($daysAgo = self::VOTE_DAYS; $daysAgo >= 0; $daysAgo--) {
            $progress = (self::VOTE_DAYS - $daysAgo) / self::VOTE_DAYS;
            $dayVotes = (int)round(
                self::VOTES_PER_DAY_MIN + $progress * (self::VOTES_PER_DAY_MAX - self::VOTES_PER_DAY_MIN)
                + random_int(-2, 2)
            );
            $dayVotes = max(1, $dayVotes);

            $date = new \DateTimeImmutable("$daysAgo days ago")->format('Y-m-d');

            for ($v = 0; $v < $dayVotes; $v++) {
                $sessionId = 'seed-' . $sessionCounter++;
                $yettiId = $yettiIds[array_rand($yettiIds)];
                $vote = random_int(0, 2) === 0 ? -1 : 1;
                $hour = sprintf('%02d:%02d:%02d', random_int(0, 23), random_int(0, 59), random_int(0, 59));

                $this->connection->executeStatement(
                    'INSERT OR IGNORE INTO yetti_vote (yetti_id, session_id, vote, voted_at) VALUES (?, ?, ?, ?)',
                    [$yettiId, $sessionId, $vote, "$date $hour"],
                );
                $total++;
            }
        }

        return $total;
    }
}
