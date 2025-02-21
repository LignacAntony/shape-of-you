<?php
namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfileEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $profile = $options['data'];
        $preferences = $profile->getPreferences() ?? [];
        $measurements = $profile->getMeasurements() ?? [];

        $builder
            ->add('avatarFile', FileType::class, [
                'label' => 'Avatar',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Seuls les fichiers JPG et PNG sont autorisés.',
                    ])
                ],
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Biographie',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('preferences', ChoiceType::class, [
                'label' => 'Thème',
                'choices' => [
                    'Mode Sombre' => 'dark',
                    'Mode Clair' => 'light',
                ],
                'expanded' => true, // Boutons radio
                'multiple' => false, // Une seule sélection
                'data' => $preferences['theme'] ?? 'light', // Valeur par défaut
                'mapped' => false, // Car c'est un champ JSON
            ])
            ->add('height', IntegerType::class, [
                'label' => 'Taille (cm)',
                'required' => false,
                'data' => $measurements['height'] ?? null, // Pré-remplissage
                'mapped' => false, // Car c'est un JSON
                'attr' => ['class' => 'form-control'],
            ])
            ->add('weight', IntegerType::class, [
                'label' => 'Poids (kg)',
                'required' => false,
                'data' => $measurements['weight'] ?? null, // Pré-remplissage
                'mapped' => false, // Car c'est un JSON
                'attr' => ['class' => 'form-control'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'hidden mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
