<?php

namespace App\Form;

use App\Entity\OutfitItem;
use App\Entity\Outfit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class OutfitItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('outfits', EntityType::class, [
                'class' => Outfit::class,
                'choice_label' => 'name',
                'label' => 'Tenues',
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('o')
                        ->where('o.author = :user')
                        ->setParameter('user', $options['user'])
                        ->orderBy('o.name', 'ASC');
                },
                'by_reference' => false,
            ])
        ;
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
