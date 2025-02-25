<?php

namespace App\Controller;

use App\Entity\CategoryItem;
use App\Entity\ClothingItem;
use App\Entity\Outfit;
use App\Entity\OutfitItem;
use App\Form\ClothingOutfitItemType;
use App\Form\ImageUploadType;
use Doctrine\ORM\EntityManagerInterface;
use OpenAI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageAnalysisController extends AbstractController
{
    #[Route('/outfit/{id}/analyze', name: 'analyze_image', methods: ['GET', 'POST'])]
    public function analyzeImage(Outfit $outfit, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier la clé API OpenAI
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? null;
        if (!$apiKey) {
            throw new \RuntimeException('La clé API OpenAI est manquante.');
        }

        // Création du formulaire d'upload d'image
        $imageForm = $this->createForm(ImageUploadType::class);
        $imageForm->handleRequest($request);

        $analysis = [];
        $error = null;
        $clothingForms = [];
        $clothingFormViews = [];

        // Traitement du formulaire d'image
        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            $file = $imageForm->get('image')->getData();

            if ($file) {
                try {
                    // Lecture et conversion de l'image en base64
                    $imageData = file_get_contents($file->getPathname());
                    $base64Image = base64_encode($imageData);
                    $imageMimeType = $file->getMimeType();

                    $client = OpenAI::client($apiKey);
                    $response = $client->chat()->create([
                        'model' => 'gpt-4o',
                        'messages' => [
                            [
                                "role" => "system",
                                "content" => "Tu es un expert en mode et vêtements. Fournis une analyse détaillée des vêtements visibles dans l'image.",
                            ],
                            [
                                "role" => "user",
                                "content" => [
                                    [
                                        "type" => "text",
                                        "text" => "Analyse l'image et retourne un **JSON strictement valide**, sous la forme d'un **tableau** (array) si plusieurs vêtements sont détectés.
Chaque vêtement doit être un objet JSON contenant :
[
    {
        'name': 'Nom du vêtement identifiable (ex: T-shirt Adidas Classic)',
        'category': 'Une des catégories suivantes : [T-shirts, Chemises, Débardeurs, Pulls, Robes, Manteaux, Vestes, Pantalons, Shorts, Jupes, Chaussures, Bottes, Sandales, Accessoires, Ceintures, Écharpes, Maillots de bain, Lingerie, Pyjamas]',
        'description': 'Une brève description du vêtement, son style, sa coupe et ses matériaux',
        'brand': 'Si une marque est identifiable, sinon null',
        'color': 'Code couleur en format HEXA (ex: #FF5733)',
        'price': 'Prix estimé du vêtement en euros (ex: 49.99)',
        'size': 'Taille du vêtement (ex: XS, S, M, L, XL, XXL)'
    }
]
Réponds **UNIQUEMENT** avec un tableau JSON valide et rien d'autre. Ne mets aucun texte avant ou après."
                                    ],
                                    [
                                        "type" => "image_url",
                                        "image_url" => [
                                            "url" => "data:$imageMimeType;base64,$base64Image",
                                        ]
                                    ],
                                ],
                            ],
                        ],
                        'max_tokens' => 700,
                        'temperature' => 0.5,
                    ]);

                    // Récupérer et nettoyer la réponse JSON
                    $jsonResponse = $response->choices[0]->message->content ?? '[]';
                    $jsonResponse = trim($jsonResponse);
                    $jsonResponse = preg_replace('/^```json\s*|\s*```$/', '', $jsonResponse);
                    preg_match('/\[.*\]/s', $jsonResponse, $matches);
                    $jsonResponse = $matches[0] ?? '[]';

                    $analysis = json_decode($jsonResponse, true);

                    if (!$analysis || !is_array($analysis) || empty($analysis)) {
                        file_put_contents('logs/openai_response.log', "Réponse brute : " . $jsonResponse . "\n", FILE_APPEND);
                        $error = 'Erreur lors du traitement des données. Vérifie le log openai_response.log.';
                    }
                } catch (\Exception $e) {
                    $error = 'Erreur lors de l\'analyse de l\'image : ' . $e->getMessage();
                }
            } else {
                $error = "Veuillez télécharger une image valide.";
            }
        }

        // Si une analyse est disponible, créer un formulaire pour chaque vêtement détecté
        if (!empty($analysis) && is_array($analysis)) {
            foreach ($analysis as $index => $clothingData) {
                // Créer et pré-remplir une instance de ClothingItem
                $clothingItem = new ClothingItem();
                $clothingItem->setName($clothingData['name'] ?? '');
                $clothingItem->setBrand($clothingData['brand'] ?? '');
                $clothingItem->setColor($clothingData['color'] ?? '');
                $clothingItem->setPrice($clothingData['price'] ?? 0);
                $clothingItem->setDescription($clothingData['description'] ?? '');

                $categoryName = $clothingData['category'] ?? null;
                if ($categoryName) {
                    $category = $entityManager->getRepository(CategoryItem::class)->findOneBy(['name' => $categoryName]);
                    if ($category) {
                        $clothingItem->setCategory($category);
                    }
                }

                // Créer l'OutfitItem associé et le lier à l'Outfit courant
                $outfitItem = new OutfitItem();
                $outfitItem->addOutfit($outfit);
                $outfitItem->setSize($clothingData['size'] ?? 'sm');
                $outfitItem->setWardrobe($outfit->getWardrobe());
                $outfitItem->setClothingItem($clothingItem);

                // Création du formulaire combiné pour ce vêtement
                $clothingForm = $this->createForm(ClothingOutfitItemType::class, [
                    'clothingItem' => $clothingItem,
                    'outfitItem'   => $outfitItem,
                ]);
                $clothingForm->handleRequest($request);

                // Si le formulaire est soumis et valide, persister les données
                if ($clothingForm->isSubmitted() && $clothingForm->isValid()) {
                    $data = $clothingForm->getData();
                    $newClothing = $data['clothingItem'];
                    $newOutfitItem = $data['outfitItem'];

                    $entityManager->persist($newClothing);
                    $entityManager->persist($newOutfitItem);
                }

                $clothingForms[] = $clothingForm;
            }
        }

        // Si une soumission des formulaires de vêtements a eu lieu, exécuter le flush et rediriger
        if ($request->isMethod('POST') && !empty($analysis)) {
            $entityManager->flush();
            return $this->redirectToRoute('analyze_image', ['id' => $outfit->getId()]);
        }

        // Préparer la vue de chaque formulaire de vêtement
        foreach ($clothingForms as $key => $clothingForm) {
            $clothingFormViews[$key] = $clothingForm->createView();
        }

        return $this->render('analysis/analyze.html.twig', [
            'outfit'         => $outfit,
            'imageForm'      => $imageForm->createView(),
            'clothingForms'  => $clothingFormViews,
            'error'          => $error,
            'analysis'       => $analysis,
        ]);
    }
}
