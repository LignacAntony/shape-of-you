<?php

namespace App\Controller;

use App\Form\ProfileEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProfileRepository;
use App\Entity\Profile;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile_show', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $entityManager, Security $security, ProfileRepository $profileRepository): Response
    {
        $user = $security->getUser();

        $profile = $profileRepository->findOneBy(['appUser' => $user]);

        if (!$profile) {
            $profile = new Profile();
            $profile->setAppUser($user);
            $entityManager->persist($profile);
            $entityManager->flush();
        }

        $form = $this->createForm(ProfileEditType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedTheme = $form->get('preferences')->getData();
            $avatarFile = $form->get('avatarFile')->getData();

            if ($avatarFile) {
                $profile->setAvatarFile($avatarFile);
            }

            $newHeight = $form->get('height')->getData();
            $newWeight = $form->get('weight')->getData();

            $preferences = $profile->getPreferences();
            $preferences['theme'] = $selectedTheme;
            $profile->setPreferences($preferences);

            $measurements = $profile->getMeasurements();
            $measurements['height'] = $newHeight;
            $measurements['weight'] = $newWeight;
            $profile->setMeasurements($measurements);

            $entityManager->persist($profile);
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('profile_show');
        }

        return $this->render('profile/profile.html.twig', [
            'profile' => $profile,
            'form' => $form->createView(),
        ]);
    }
}
