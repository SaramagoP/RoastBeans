<?php

namespace App\Form;


use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EditOrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $times = ["09:00"=>"09:00", "09:15"=>"09:15", "09:30"=>"09:30", "09:45"=>"09:45", "10:00"=>"10:00","10:15"=>"10:15","10:30"=>"10:30","10:45"=>"10:45","11:00"=>"11:00","11:15"=>"11:15","11:30"=>"11:30","12:00"=>"12:00","12:15"=>"12:15","12:30"=>"12:30","12:45"=>"12:45","13:00"=>"13:00","13:15"=>"13:15","13:30"=>"13:30","13:45"=>"13:45","14:00"=>"14:00","14:15"=>"14:15","14:30"=>"14:30","14:45"=>"14:45","15:00"=>"15:00","15:15"=>"15:15","15:30"=>"15:30","15:45"=>"15:45","16:00"=>"16:00","16:15"=>"16:15","16:30"=>"16:30","16:45"=>"16:45","17:00"=>"17:00","17:15"=>"17:15","17:30"=>"17:30","17:45"=>"17:45",];

        $builder
            ->add('pickupFirstName', TextType::class)
            ->add('pickupLastName', TextType::class)
            ->add('pickup_time', ChoiceType::class, [
                'choices'  => $times,
                'placeholder' => "Sélectionnez l'heure",
                'constraints' => [
                    new Choice([
                        'choices'  => $times,
                        "match" => true,
                        "message" => "L\'heure de récupération doit être comprise entre 09:00 et 18:00.",
                        ])
                ],
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
