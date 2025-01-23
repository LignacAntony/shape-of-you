<?php

namespace App\Form;

use App\Entity\ClothingItem;
use App\Entity\Outfit;
use App\Entity\OutfitItem;
use App\Entity\Wardrobe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutfitItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('size')
            ->add('purchaseAt', null, [
                'widget' => 'single_text',
            ])
            ->add('outfit', EntityType::class, [
                'class' => Outfit::class,
                'choice_label' => 'id',
            ])
            ->add('clothingItem', EntityType::class, [
                'class' => ClothingItem::class,
                'choice_label' => 'id',
            ])
            ->add('wardrobe', EntityType::class, [
                'class' => Wardrobe::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OutfitItem::class,
        ]);
    }
}
