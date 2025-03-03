<?php

namespace App\Controller;

use App\Entity\Wardrobe;
use App\Entity\Outfit;
use App\Entity\OutfitItem;
use App\Form\OutfitPropositionType;
use Doctrine\ORM\EntityManagerInterface;
use OpenAI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutfitProposalController extends AbstractController
{
    #[Route('/wardrobe/{id}/proposal-outfit', name: 'proposal_outfit', methods: ['GET', 'POST'])]
    public function proposeAndCreateOutfit(Wardrobe $wardrobe, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $wardrobe->getAuthor()) {
            return $this->redirectToRoute('app_home');
        }

        // Partie 1 : Formulaire de proposition via l'IA (reste inchangée)
        $formProposal = $this->createFormBuilder()
            ->add('demande', TextType::class, [
                'label' => 'Décrivez l\'outfit souhaité',
                'required' => true,
            ])
            ->getForm();
        $formProposal->handleRequest($request);

        $proposedOutfit = null;
        $error = null;

        if ($formProposal->isSubmitted() && $formProposal->isValid()) {
            $data = $formProposal->getData();
            $demande = $data['demande'];

            $items = [];
            foreach ($wardrobe->getOutfitItems() as $wardrobeItem) {
                $clothing = $wardrobeItem->getClothingItem();
                if ($clothing) {
                    $items[] = [
                        'name' => $clothing->getName(),
                        'description' => $clothing->getDescription(),
                        'category' => $clothing->getCategory() ? $clothing->getCategory()->getName() : null,
                        'brand' => $clothing->getBrand(),
                        'color' => $clothing->getColor(),
                        'price' => $clothing->getPrice(),
                    ];
                }
            }

            $promptSystem = "Tu es un expert en mode.";
            $promptUser = "Voici les vêtements disponibles (JSON) : " . json_encode($items) . ".\n";
            $promptUser .= "La demande est : \"$demande\".\n";
            $promptUser .= "Réponds UNIQUEMENT avec un tableau JSON strictement valide. Chaque objet doit contenir au minimum les clés 'name', 'category' et 'reason'.";

            try {
                $client = OpenAI::client($_ENV['OPENAI_API_KEY'] ?? null);
                $responseAI = $client->chat()->create([
                    'model' => 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => $promptSystem],
                        ['role' => 'user', 'content' => $promptUser],
                    ],
                    'max_tokens' => 700,
                    'temperature' => 0.6,
                ]);

                $jsonResponse = $responseAI->choices[0]->message->content ?? '[]';
                $jsonResponse = trim($jsonResponse);
                $jsonResponse = preg_replace('/^```json\s*|\s*```$/', '', $jsonResponse);
                preg_match('/\[(.*)\]/s', $jsonResponse, $matches);
                if (isset($matches[0])) {
                    $jsonResponse = $matches[0];
                }
                $proposedOutfit = json_decode($jsonResponse, true);
                if (!is_array($proposedOutfit)) {
                    $error = "Erreur lors de la proposition de l'outfit.";
                }
            } catch (\Exception $e) {
                $error = "Erreur lors de l'appel à l'IA : " . $e->getMessage();
            }
        }

        // Partie 2 : Création de l'outfit
        $formOutfit = null;
        if ($proposedOutfit && is_array($proposedOutfit)) {
            // Créer une nouvelle instance d'Outfit
            $outfit = new Outfit();
            $outfit->setWardrobe($wardrobe);
            $outfit->setAuthor($this->getUser());
            $outfit->setName("Nouvelle tenue proposée");
            $outfit->setDescription(""); // Valeur par défaut

            // Pour chaque élément proposé, rechercher dans la wardrobe le ClothingItem correspondant (par nom)
            $selectedClothingItems = [];
            foreach ($proposedOutfit as $itemProposal) {
                foreach ($wardrobe->getOutfitItems() as $wardrobeItem) {
                    $clothing = $wardrobeItem->getClothingItem();
                    if ($clothing && strcasecmp($clothing->getName(), $itemProposal['name']) === 0) {
                        $selectedClothingItems[] = $clothing;
                        break;
                    }
                }
            }

            // Utilisation du formulaire lié à l'entité Outfit
            $formOutfit = $this->createForm(OutfitPropositionType::class, $outfit);
            $formOutfit->handleRequest($request);

            if ($formOutfit->isSubmitted() && $formOutfit->isValid()) {
                // L'entité $outfit est automatiquement mise à jour avec les valeurs du formulaire
                // Pour chaque ClothingItem sélectionné, on crée et associe un OutfitItem
                foreach ($selectedClothingItems as $clothing) {
                    $outfitItem = new OutfitItem();
                    $outfitItem->setClothingItem($clothing);
                    $outfitItem->setWardrobe($wardrobe);
                    $outfitItem->setSize('M'); // Taille par défaut, à modifier si besoin
                    $outfit->addOutfitItem($outfitItem);
                }

                $entityManager->persist($outfit);
                $entityManager->flush();
                $this->addFlash('success', 'Votre outfit a été créé avec succès.');
                return $this->redirectToRoute('outfit_show', ['id' => $outfit->getId()]);
            }
        }

        return $this->render('outfit/proposal_and_create.html.twig', [
            'formProposal'   => $formProposal->createView(),
            'proposedOutfit' => $proposedOutfit,
            'error'          => $error,
            'formOutfit'     => $formOutfit ? $formOutfit->createView() : null,
            'wardrobe'       => $wardrobe,
        ]);
    }
}
