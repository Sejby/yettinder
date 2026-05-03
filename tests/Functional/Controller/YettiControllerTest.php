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
            'name'    => 'Karel',
            'gender'  => 'male',
            'height'  => '182',
            'weight'  => '80',
            'address' => 'Brno',
            'rating'  => '4.0',
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
            'name'    => '',
            'gender'  => 'male',
            'height'  => '175',
            'weight'  => '70',
            'address' => 'Praha',
            'rating'  => '3.0',
        ]);

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Jméno je povinné');
    }

    public function testSubmitFormWithInvalidCsrfShowsError(): void
    {
        $this->client->request('POST', '/yetti/new', [
            '_token' => 'invalid-token',
            'name'   => 'Karel',
            'gender' => 'male',
        ]);

        $this->assertSelectorTextContains('body', 'bezpečnostní token');
    }
}
