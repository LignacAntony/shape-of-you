<?php

namespace App\Form;

use App\Entity\ClothingItem;
use App\Entity\OutfitItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType as SymfonyFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClothingOutfitItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*
         * Ici, nous n’avons pas directement un seul "data_class"
         * parce qu’on veut gérer 2 objets différents en même temps.
         * On va donc créer des sous-champs pour clothingItem et outfitItem.
         */
        $builder
            ->add('clothingItem', ClothingItemIaType::class, [
                // on peut désactiver data_class si on veut manipuler
                // un array. Mais si on passe un objet, on peut laisser
                // le ClothingItemType normal, qui lui a son data_class.
            ])
            ->add('outfitItem', OutfitItemIaType::class, [
                // idem, on délègue à OutfitItemType la configuration
                // des champs de OutfitItem.
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        /*
         * On attend un tableau comme data:
         * [
         *   'clothingItem' => (ClothingItem),
         *   'outfitItem' => (OutfitItem)
         * ]
         */
        $resolver->setDefaults([
            'data_class' => null,
            // on ne lie pas ce form à une entité unique,
            // mais à un "tableau" contenant 2 entités.
        ]);
    }
}
