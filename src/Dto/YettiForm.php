<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class YettiForm
{
    #[Assert\NotBlank(message: 'Jméno je povinné.', normalizer: 'trim')]
    #[Assert\Length(max: 100, maxMessage: 'Jméno může mít nejvýše 100 znaků.')]
    public string $name = '';

    #[Assert\Choice(choices: ['male', 'female', 'other'], message: 'Neplatné pohlaví.')]
    public string $gender = '';

    #[Assert\Range(
        notInRangeMessage: 'Výška musí být mezi {{ min }} a {{ max }} cm.',
        min: 50,
        max: 350,
    )]
    public int $height = 0;

    #[Assert\Range(
        notInRangeMessage: 'Váha musí být mezi {{ min }} a {{ max }} kg.',
        min: 10.0,
        max: 500.0,
    )]
    public float $weight = 0.0;

    #[Assert\NotBlank(message: 'Bydliště je povinné.', normalizer: 'trim')]
    #[Assert\Length(max: 120, maxMessage: 'Bydliště může mít nejvýše 120 znaků.')]
    public string $address = '';

    #[Assert\Range(
        notInRangeMessage: 'Hodnocení musí být mezi {{ min }} a {{ max }}.',
        min: 0.0,
        max: 5.0,
    )]
    public float $rating = 0.0;
}
