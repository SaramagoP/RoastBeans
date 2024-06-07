<?php

namespace App\Form;


use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class EditOrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pickupFirstName', TextType::class)
            ->add('pickupLastName', TextType::class)
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
            'data_class' => Order::class,
        ]);
    }
}
