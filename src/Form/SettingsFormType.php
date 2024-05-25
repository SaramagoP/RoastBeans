<?php

namespace App\Form;

use App\Entity\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class SettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('websiteName', TextType::class)
            ->add('websiteUrl', UrlType::class)
            ->add('description', TextType::class)
            ->add('email', EmailType::class)
            ->add('phone', TelType::class)
            ->add('adresse', TextType::class)
            ->add('city', TextType::class)
            ->add('country', CountryType::class, [
                "placeholder" => "Selectionnez un pays"
            ])
            ->add('postalCode', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Settings::class,
        ]);
    }
}
