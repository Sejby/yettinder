<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Yetti;
use Symfony\Component\Validator\Constraints as Assert;

final class YettiInput
{
    #[Assert\NotBlank(message: 'Jméno je povinné.', normalizer: 'trim')]
    #[Assert\Length(max: 100, maxMessage: 'Jméno může mít nejvýše 100 znaků.')]
    public string $name = '';

    #[Assert\Choice(choices: ['male', 'female', 'other'], message: 'Neplatné pohlaví.')]
    public string $gender = '';

    #[Assert\Range(
        notInRangeMessage: 'Výška musí být mezi {{ min }} a {{ max }} cm.',
        invalidMessage: 'Výška musí být číslo.',
        min: 50,
        max: 350,
    )]
    public string $height = '';

    #[Assert\Range(
        notInRangeMessage: 'Váha musí být mezi {{ min }} a {{ max }} kg.',
        invalidMessage: 'Váha musí být číslo.',
        min: 10.0,
        max: 500.0,
    )]
    public string $weight = '';

    #[Assert\NotBlank(normalizer: 'trim', message: 'Bydliště je povinné.')]
    #[Assert\Length(max: 120, maxMessage: 'Bydliště může mít nejvýše 120 znaků.')]
    public string $address = '';

    #[Assert\Range(
        notInRangeMessage: 'Hodnocení musí být mezi {{ min }} a {{ max }}.',
        invalidMessage: 'Hodnocení musí být číslo.',
        min: 0.0,
        max: 5.0,
    )]
    public string $rating = '';

    public function toYetti(): Yetti
    {
        return new Yetti(
            id: null,
            name: trim($this->name),
            gender: $this->gender,
            height: (int) $this->height,
            weight: (float) $this->weight,
            address: trim($this->address),
            rating: (float) $this->rating,
        );
    }
}
