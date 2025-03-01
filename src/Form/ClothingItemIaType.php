<?php
// src/Form/ClothingItemType.php
namespace App\Form;

use App\Entity\ClothingItem;
use App\Entity\CategoryItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class ClothingItemIaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Nom du vêtement',
                'attr' => [
                    'placeholder' => 'Nom du vêtement',
                    'class' => 'form-area w-full py-2 mb-2',
                ],
            ])
            ->add('brand', TextType::class, [
                'required' => false,
                'label' => 'Marque',
                'attr' => [
                    'placeholder' => 'Marque du vêtement',
                    'class' => 'form-area w-full py-2 mb-2',
                ],
            ])
            ->add('color', ColorType::class, [
                'required' => false,
                'label' => 'Couleur : ',
                'attr' => [
                    'placeholder' => 'Couleur du vêtement',
                    'class' => 'mb-2 ',
                ],
            ])
            ->add('price', MoneyType::class, [
                'currency' => 'EUR',
                'required' => false,
                'label' => 'Prix ',
                'divisor' => 100,
                'attr' => [
                    'placeholder' => 'Prix du vêtement',
                    'class' => 'form-area w-full py-2 mb-2',
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Description du vêtement',
                    'class' => 'form-area w-full py-2 mb-2',
                ],
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => CategoryItem::class,
                'choice_label' => 'name',
                'placeholder' => 'Sélectionnez une catégorie',
                'attr' => [
                    'class' => 'form-area w-full py-2 mb-2',
                ],
                'required' => true,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du vêtement',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-area w-full py-2 mb-2',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClothingItem::class,
        ]);
    }
}
