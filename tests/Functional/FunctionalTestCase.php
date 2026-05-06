<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class FunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    /**
     * @throws \Exception
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $kernel = static::bootKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->run(
            new ArrayInput(['command' => 'doctrine:migrations:migrate', '--no-interaction' => true]),
            new NullOutput(),
        );
        static::ensureKernelShutdown();
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->clearDatabase();
    }

    /**
     * @throws Exception
     */
    protected function clearDatabase(): void
    {
        $connection = $this->getConnection();
        $connection->executeStatement('DELETE FROM yetti_vote');
        $connection->executeStatement('DELETE FROM yetti');
    }

    protected function getConnection(): Connection
    {
        return $this->client->getContainer()->get('doctrine.dbal.default_connection');
    }

    /**
     * @throws Exception
     */
    protected function insertYetti(
        string $name = 'Test',
        string $gender = 'male',
        int    $height = 175,
        float  $weight = 70.0,
        string $address = 'Praha',
        float  $rating = 3.0,
    ): int
    {
        $this->getConnection()->insert('yetti', compact('name', 'gender', 'height', 'weight', 'address', 'rating'));

        return (int)$this->getConnection()->lastInsertId();
    }
}
