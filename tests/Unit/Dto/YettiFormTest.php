<?php

declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\YettiForm;
use PHPUnit\Framework\TestCase;

final class YettiFormTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $form = new YettiForm();

        $this->assertSame('', $form->name);
        $this->assertSame('', $form->gender);
        $this->assertSame(0, $form->height);
        $this->assertSame(0.0, $form->weight);
        $this->assertSame('', $form->address);
        $this->assertSame(0.0, $form->rating);
    }
}
