<?php

namespace App\Form;

use App\Entity\Outfit;
use App\Entity\Wardrobe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutfitIaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'outfit',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('wardrobe', EntityType::class, [
                'class' => Wardrobe::class,
                'choice_label' => 'name', // À adapter selon la propriété à afficher
                'label' => 'Choisir la Wardrobe',
                'placeholder' => 'Sélectionnez une Wardrobe',
                // 'required' => false,
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publier ?',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Outfit::class,
        ]);
    }
}
