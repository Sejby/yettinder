<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\YettiInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<YettiInput> */
final class YettiInputType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Jméno', 'empty_data' => ''])
            ->add('gender', ChoiceType::class, [
                'label' => 'Pohlaví',
                'choices' => ['Muž' => 'male', 'Žena' => 'female', 'Jiné' => 'other'],
                'placeholder' => 'Vyber...',
            ])
            ->add('height', IntegerType::class, ['label' => 'Výška (cm)', 'empty_data' => 0])
            ->add('weight', NumberType::class, ['label' => 'Váha (kg)', 'scale' => 1, 'empty_data' => '0'])
            ->add('address', TextType::class, ['label' => 'Bydliště', 'empty_data' => ''])
            ->add('rating', NumberType::class, ['label' => 'Hodnocení (0–5)', 'scale' => 1, 'empty_data' => '0']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'   => YettiInput::class,
            'csrf_message' => 'Neplatný bezpečnostní token. Zkuste prosím znovu.',
        ]);
    }
}
