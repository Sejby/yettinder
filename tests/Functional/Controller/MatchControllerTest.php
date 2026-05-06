<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\FunctionalTestCase;
use Doctrine\DBAL\Exception;

final class MatchControllerTest extends FunctionalTestCase
{
    public function testMatchPageReturns200(): void
    {
        $this->client->request('GET', '/match');

        $this->assertResponseIsSuccessful();
    }

    public function testMatchPageShowsEmptyMessageWhenNoYettis(): void
    {
        $this->client->request('GET', '/match');

        $this->assertSelectorTextContains('body', 'žádný Yetti');
    }

    /**
     * @throws Exception
     */
    public function testMatchPageShowsYettiWhenAvailable(): void
    {
        $this->insertYetti(name: 'Tereza');

        $this->client->request('GET', '/match');

        $this->assertSelectorTextContains('body', 'Tereza');
    }

    /**
     * @throws Exception
     */
    public function testMatchPageShowsBothVoteButtons(): void
    {
        $this->insertYetti();

        $crawler = $this->client->request('GET', '/match');

        $this->assertGreaterThanOrEqual(1, $crawler->selectButton('Super!')->count());
        $this->assertGreaterThanOrEqual(1, $crawler->selectButton('Pas')->count());
    }

    /**
     * @throws Exception
     */
    public function testVoteRedirectsToMatchPage(): void
    {
        $this->insertYetti(name: 'Jarda');

        $crawler = $this->client->request('GET', '/match');
        $form = $crawler->selectButton('Super!')->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('/match');
    }

    /**
     * @throws Exception
     */
    public function testAfterVotingOnlyYettiAllRatedMessageIsShown(): void
    {
        $this->insertYetti(name: 'Jediny');

        $crawler = $this->client->request('GET', '/match');
        $form = $crawler->selectButton('Super!')->form();
        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'Prošel jsi všechny');
    }

    /**
     * @throws Exception
     */
    public function testVoteWithInvalidCsrfReturns403(): void
    {
        $id = $this->insertYetti();

        $this->client->request('POST', "/match/$id/vote", [
            '_token' => 'invalid-token',
            'vote' => '1',
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @throws Exception
     */
    public function testVoteForNonExistentYettiReturns404(): void
    {
        $this->insertYetti();
        $crawler = $this->client->request('GET', '/match');
        $token = $crawler->filter('input[name="_token"]')->first()->attr('value');

        $this->client->request('POST', '/match/9999/vote', [
            '_token' => $token,
            'vote' => '1',
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @throws Exception
     */
    public function testVoteWithInvalidVoteValueReturns404(): void
    {
        $id = $this->insertYetti();
        $crawler = $this->client->request('GET', '/match');

        $token = $crawler->filter('input[name="_token"]')->first()->attr('value');

        $this->client->request('POST', "/match/$id/vote", [
            '_token' => $token,
            'vote' => '999',
        ]);

        $this->assertResponseStatusCodeSame(404);
    }
}
