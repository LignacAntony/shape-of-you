<?php
// src/Form/ClothingItemType.php
namespace App\Form;

use App\Entity\ClothingItem;
use App\Entity\CategoryItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class ClothingItemIaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('brand', TextType::class)
            ->add('color', TextType::class)
            ->add('price', MoneyType::class, [
                'currency' => 'EUR',
            ])
            ->add('description', TextType::class, [
                'required' => false,
            ])
            ->add('category', EntityType::class, [
                'class' => CategoryItem::class,
                'choice_label' => 'name',
                'placeholder' => 'Sélectionnez une catégorie',
                'required' => true,
            ])
            ->add('image', FileType::class, [
                'label'    => 'Image du vêtement',
                'mapped'   => false, // On gère l'upload manuellement dans le contrôleur
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClothingItem::class,
        ]);
    }
}
