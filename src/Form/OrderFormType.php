<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;


class OrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var User
         */
        $user = $options['user'];

        $builder
        ->add('order_date', DateType::class, [
            'widget' => 'single_text',
            'required' => true,
        ])
        ->add('pickup_time', TimeType::class, [
            'widget' => 'single_text',
            'required' => true,
        ])
        ->add('pickup_date', DateType::class, [
            'widget' => 'single_text',
            'required' => true,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "user" => []
        ]);
    }
}
