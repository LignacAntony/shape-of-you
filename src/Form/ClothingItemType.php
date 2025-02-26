<?php

namespace App\Form;

use App\Entity\CategoryItem;
use App\Entity\ClothingItem;
use App\Entity\Outfit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;
use App\Entity\User;
use App\Entity\Wardrobe;

class ClothingItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('brand', TextType::class, [
                'label' => 'Marque',
                'required' => false,
            ])
            ->add('color', ColorType::class, [
                'label' => 'Couleur',
                'required' => false,
            ])
            ->add('size', ChoiceType::class, [
                'label' => 'Taille',
                'required' => true,
                'mapped' => false,
                'choices' => [
                    'XS' => 'XS',
                    'S' => 'S',
                    'M' => 'M',
                    'L' => 'L',
                    'XL' => 'XL',
                    'XXL' => 'XXL',
                ],
                'placeholder' => 'Choisir une taille',
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'required' => false,
                'currency' => 'EUR',
                'divisor' => 100,
            ])
            ->add('category', EntityType::class, [
                'class' => CategoryItem::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'required' => true,
            ])
            ->add('images', FileType::class, [
                'label' => 'Images',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/*',
                    'multiple' => 'multiple',
                    'data-controller' => 'image-preview',
                ],
                'constraints' => [
                    new All([
                        new Image([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/webp'
                            ],
                            'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou WEBP)',
                        ])
                    ])
                ],
            ])
        ;

        // Ajouter le champ outfit si une garde-robe est spécifiée
        if ($options['wardrobe']) {
            $builder->add('outfit', EntityType::class, [
                'class' => Outfit::class,
                'choice_label' => 'name',
                'label' => 'Ajouter à une tenue',
                'required' => false,
                'mapped' => false,
                'data' => $options['default_outfit'],
                'query_builder' => function ($er) use ($options) {
                    return $er->createQueryBuilder('o')
                        ->where('o.wardrobe = :wardrobe')
                        ->andWhere('o.author = :user')
                        ->setParameter('wardrobe', $options['wardrobe'])
                        ->setParameter('user', $options['user'])
                        ->orderBy('o.name', 'ASC');
                },
                'placeholder' => 'Sélectionner une tenue (optionnel)',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClothingItem::class,
            'user' => null,
            'wardrobe' => null,
            'default_outfit' => null,
        ]);

        $resolver->setAllowedTypes('user', ['null', User::class]);
        $resolver->setAllowedTypes('wardrobe', ['null', Wardrobe::class]);
        $resolver->setAllowedTypes('default_outfit', ['null', Outfit::class]);
    }
}
