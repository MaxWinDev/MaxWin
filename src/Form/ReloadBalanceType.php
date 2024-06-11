<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReloadBalanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'currency' => 'EUR',
                'label' => 'Montant'
            ]);

        if ($options['action'] === 'deposit') {
            $builder
                ->add('currency', ChoiceType::class, [
                    'choices' => [
                        'EUR' => 'EUR',
                        'USD' => 'USD',
                        'GBP' => 'GBP',
                    ],
                    'label' => 'Devise'
                ]);
        }

        if ($options['action'] === 'withdraw') {
            $builder
                ->add('currency', ChoiceType::class, [
                    'choices' => [
                        'EUR' => 'EUR',
                        'USD' => 'USD',
                        'GBP' => 'GBP',
                    ],
                    'label' => 'Devise'
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
