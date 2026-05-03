<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\FunctionalTestCase;
use Doctrine\DBAL\Exception;

final class StatsControllerTest extends FunctionalTestCase
{
    public function testStatsPageReturns200(): void
    {
        $this->client->request('GET', '/stats');

        $this->assertResponseIsSuccessful();
    }

    public function testStatsPageRendersAllSections(): void
    {
        $this->client->request('GET', '/stats');

        $this->assertSelectorTextContains('body', 'Podle roku');
        $this->assertSelectorTextContains('body', 'Podle měsíce');
        $this->assertSelectorTextContains('body', 'Žebříček Yetiů');
        $this->assertSelectorExists('canvas#chartDaily');
    }

    /**
     * @throws Exception
     */
    public function testStatsPageShowsYettiInRanking(): void
    {
        $this->insertYetti(name: 'Ranked');

        $this->client->request('GET', '/stats');

        $this->assertSelectorTextContains('body', 'Ranked');
    }
}
