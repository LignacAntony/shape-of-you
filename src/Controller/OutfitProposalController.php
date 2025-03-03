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

        if ($request->isMethod('GET') && ($request->query->get('reset') || !$request->headers->get('referer'))) {
            $request->getSession()->remove('proposed_outfit');
        }

        $proposedOutfit = null;
        $error = null;
        $formOutfit = null;

        $formProposal = $this->createFormBuilder()
            ->add('demande', TextType::class, [
                'label' => 'Décrivez l\'outfit souhaité',
                'required' => true,
            ])
            ->getForm();

        $sessionProposal = $request->getSession()->get('proposed_outfit');
        if ($sessionProposal) {
            $outfit = new Outfit();
            $outfit->setWardrobe($wardrobe);
            $outfit->setAuthor($this->getUser());
            $outfit->setName("Nouvelle tenue proposée");
            $outfit->setDescription("");
            $outfit->setCreatedAt(new \DateTimeImmutable());
            $outfit->setLikesCount(0);
            $outfit->setIsPublished(false);
            
            $formOutfit = $this->createForm(OutfitPropositionType::class, $outfit);
            $proposedOutfit = $sessionProposal;
        }

        $formProposal->handleRequest($request);
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

            try {
                $client = OpenAI::client($_ENV['OPENAI_API_KEY'] ?? null);
                $responseAI = $client->chat()->create([
                    'model' => 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => "Tu es un expert en mode."],
                        ['role' => 'user', 'content' => "Voici les vêtements disponibles (JSON) : " . json_encode($items) . ".\nLa demande est : \"$demande\".\nRéponds UNIQUEMENT avec un tableau JSON strictement valide. Chaque objet doit contenir au minimum les clés 'name', 'category' et 'reason'."],
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
                } else {
                    $request->getSession()->set('proposed_outfit', $proposedOutfit);
                    return $this->redirectToRoute('proposal_outfit', ['id' => $wardrobe->getId()]);
                }
            } catch (\Exception $e) {
                $error = "Erreur lors de l'appel à l'IA : " . $e->getMessage();
            }
        }

        if ($formOutfit) {
            $formOutfit->handleRequest($request);
            if ($formOutfit->isSubmitted() && $formOutfit->isValid()) {
                $selectedClothingItems = [];
                foreach ($sessionProposal as $itemProposal) {
                    foreach ($wardrobe->getOutfitItems() as $wardrobeItem) {
                        $clothing = $wardrobeItem->getClothingItem();
                        if ($clothing && strcasecmp($clothing->getName(), $itemProposal['name']) === 0) {
                            $selectedClothingItems[] = $clothing;
                            break;
                        }
                    }
                }

                foreach ($selectedClothingItems as $clothing) {
                    $outfitItem = new OutfitItem();
                    $outfitItem->setClothingItem($clothing);
                    $outfitItem->setWardrobe($wardrobe);
                    $outfitItem->setSize('M');
                    $entityManager->persist($outfitItem);
                    
                    $outfit->addOutfitItem($outfitItem);
                    $outfitItem->addOutfit($outfit);
                }

                try {
                    $entityManager->persist($outfit);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Votre outfit a été créé avec succès.');
                    return $this->redirectToRoute('proposal_outfit', [
                        'id' => $wardrobe->getId(),
                        'reset' => true
                    ]);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la création de l\'outfit.');
                }
            }
        }

        return $this->render('outfit/proposal_and_create.html.twig', [
            'formProposal' => $formProposal->createView(),
            'proposedOutfit' => $proposedOutfit,
            'error' => $error,
            'formOutfit' => $formOutfit ? $formOutfit->createView() : null,
            'wardrobe' => $wardrobe,
        ]);
    }
}
