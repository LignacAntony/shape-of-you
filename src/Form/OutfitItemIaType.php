<?php

namespace App\Form;

use App\Entity\OutfitItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class OutfitItemIaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // 'size' étant un champ modifiable par l'utilisateur
            ->add('size', TextType::class, [
                'required' => false,
                'label' => 'Taille'
            ]);
        // Vous pouvez ajouter d’autres champs éventuels sur OutfitItem
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OutfitItem::class,
        ]);
    }
}
