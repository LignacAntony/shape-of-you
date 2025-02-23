<?php

namespace App\Form;

use App\Entity\OutfitItem;
use App\Entity\Outfit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Entity\ClothingItem;
use App\Entity\Wardrobe;

class OutfitItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('size', TextType::class, [
                'label' => 'Taille',
                'required' => true,
            ])
            ->add('purchaseAt', DateType::class, [
                'label' => 'Date d\'achat',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('clothingItem', EntityType::class, [
                'class' => ClothingItem::class,
                'choice_label' => 'name',
                'label' => 'Vêtement',
                'required' => true,
            ])
            ->add('wardrobe', EntityType::class, [
                'class' => Wardrobe::class,
                'choice_label' => 'name',
                'label' => 'Garde-robe',
                'required' => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('w')
                        ->where('w.author = :user')
                        ->setParameter('user', $options['user'])
                        ->orderBy('w.name', 'ASC');
                },
            ])
        ;

        $formModifier = function (FormInterface $form, ?Wardrobe $wardrobe = null) use ($options) {
            if ($wardrobe) {
                $form->add('outfits', EntityType::class, [
                    'class' => Outfit::class,
                    'choice_label' => 'name',
                    'label' => 'Tenues disponibles',
                    'multiple' => true,
                    'expanded' => true,
                    'query_builder' => function (EntityRepository $er) use ($options, $wardrobe) {
                        return $er->createQueryBuilder('o')
                            ->where('o.author = :user')
                            ->andWhere('o.wardrobe = :wardrobe')
                            ->setParameter('user', $options['user'])
                            ->setParameter('wardrobe', $wardrobe)
                            ->orderBy('o.name', 'ASC');
                    },
                    'by_reference' => false,
                    'attr' => [
                        'class' => 'space-y-2'
                    ],
                    'choice_attr' => function() {
                        return ['class' => 'mr-2'];
                    },
                    'label_attr' => [
                        'class' => 'block text-sm font-medium text-gray-700 mb-2'
                    ],
                ]);
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $formModifier($event->getForm(), $data ? $data->getWardrobe() : null);
            }
        );

        $builder->get('wardrobe')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $wardrobe = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $wardrobe);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OutfitItem::class,
            'user' => null,
        ]);

        $resolver->setAllowedTypes('user', ['null', User::class]);
    }
}
