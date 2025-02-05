<?php

namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('avatar', TextType::class, [
                'label' => 'URL de l\'avatar',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Biographie',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('preferences', TextareaType::class, [
                'label' => 'Préférences (JSON)',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('measurements', TextareaType::class, [
                'label' => 'Mesures (JSON)',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'hidden mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded', 'id' => 'save-btn'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
