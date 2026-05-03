<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\YettiInput;
use PHPUnit\Framework\TestCase;

final class YettiInputTest extends TestCase
{
    private function validInput(): YettiInput
    {
        $input = new YettiInput();
        $input->name = 'Jan';
        $input->gender = 'male';
        $input->height = '180';
        $input->weight = '75.0';
        $input->address = 'Teplice';
        $input->rating = '4.5';

        return $input;
    }

    public function testToYettiMapsAllFields(): void
    {
        $yetti = $this->validInput()->toYetti();

        $this->assertNull($yetti->getId());
        $this->assertSame('Jan', $yetti->getName());
        $this->assertSame('male', $yetti->getGender());
        $this->assertSame(180, $yetti->getHeight());
        $this->assertSame(75.0, $yetti->getWeight());
        $this->assertSame('Teplice', $yetti->getAddress());
        $this->assertSame(4.5, $yetti->getRating());
    }
}
