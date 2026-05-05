<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\FunctionalTestCase;
use Doctrine\DBAL\Exception;

final class YettiControllerTest extends FunctionalTestCase
{
    public function testHomepageReturns200(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Yettinder');
    }

    /**
     * @throws Exception
     */
    public function testHomepageShowsBestOfTableWhenYettisExist(): void
    {
        $this->insertYetti(name: 'Franta', rating: 5.0);

        $this->client->request('GET', '/');

        $this->assertSelectorExists('table');
        $this->assertSelectorTextContains('body', 'Franta');
    }

    public function testHomepageShowsEmptyMessageWhenNoYettis(): void
    {
        $this->client->request('GET', '/');

        $this->assertSelectorTextContains('body', 'Přidej prvního');
    }

    public function testNewYettiFormReturns200(): void
    {
        $this->client->request('GET', '/yetti/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testSubmitValidFormCreatesYettiAndRedirects(): void
    {
        $crawler = $this->client->request('GET', '/yetti/new');
        $form    = $crawler->selectButton('Uložit Yettiho')->form([
            'yetti_input[name]'    => 'Karel',
            'yetti_input[gender]'  => 'male',
            'yetti_input[height]'  => '182',
            'yetti_input[weight]'  => '80',
            'yetti_input[address]' => 'Brno',
            'yetti_input[rating]'  => '4.0',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/yetti/new');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Karel');
    }

    public function testSubmitFormWithMissingNameShowsError(): void
    {
        $crawler = $this->client->request('GET', '/yetti/new');
        $form    = $crawler->selectButton('Uložit Yettiho')->form([
            'yetti_input[name]'    => '',
            'yetti_input[gender]'  => 'male',
            'yetti_input[height]'  => '175',
            'yetti_input[weight]'  => '70',
            'yetti_input[address]' => 'Praha',
            'yetti_input[rating]'  => '3.0',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('.invalid-feedback', 'Jméno je povinné');
    }

    public function testSubmitFormWithInvalidCsrfShowsError(): void
    {
        $this->client->request('POST', '/yetti/new', [
            'yetti_input' => [
                '_token' => 'invalid-token',
                'name'   => 'Karel',
                'gender' => 'male',
            ],
        ]);

        $this->assertSelectorTextContains('.alert-danger', 'bezpečnostní token');
    }
}
