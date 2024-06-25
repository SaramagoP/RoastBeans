<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdminProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Ajouter un champ de texte pour le champ 'name'
            ->add('name', TextType::class)

            // Ajouter une liste déroulante pour le champ 'category' en utilisant EntityType
            ->add('category', EntityType::class, [
                'class' => Category::class,  // Spécifie la classe de l'entité
                'choice_label' => 'name', // Utilise la propriété 'name' de Category comme étiquette
                'multiple' => false, // Ne permet pas de sélection multiple
                'expanded' => false, // Affiche comme une liste déroulante
                'placeholder' => "Choississez une catégorie" // Texte par défaut lorsque rien n'est sélectionné
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false, // Rend le téléchargement de l'image optionnel
                'allow_delete' => true, // Permet de supprimer l'image
                'delete_label' => "Supprimer l'image", // Étiquette pour la suppression
                'download_label' => false, // Désactive l'étiquette de téléchargement
                'download_uri' => false, // Désactive l'URI de téléchargement
                'image_uri' => false, // Désactive l'URI de l'image
                'imagine_pattern' =>  false, // Désactive les motifs d'imagine
                'asset_helper' => false,  // Désactive l'aide aux actifs
            ])
            // Ajouter un champ de zone de texte pour le champ 'description'
            ->add('description', TextareaType::class)

            // Ajouter un champ numérique pour le champ 'price'
            ->add('price', NumberType::class)

            // Ajouter un champ numérique pour le champ 'quantity'
            ->add('quantity', NumberType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Configurer les options par défaut du formulaire
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
