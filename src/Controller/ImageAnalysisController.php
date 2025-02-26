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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageAnalysisController extends AbstractController
{
    #[Route('/outfit/{id}/analyze', name: 'analyze_image', methods: ['GET', 'POST'])]
    public function analyzeImage(Outfit $outfit, Request $request, EntityManagerInterface $entityManager): Response
    {
        // On récupère la session pour stocker temporairement l'analyse
        $session = $request->getSession();

        // Création et gestion du formulaire d'upload d'image
        $imageForm = $this->createForm(ImageUploadType::class);
        $imageForm->handleRequest($request);

        $analysis = [];
        $error = null;
        $globalForm = null;

        // Si le formulaire d'image est soumis et valide, effectuer l'analyse
        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            $file = $imageForm->get('image')->getData();

            if ($file) {
                try {
                    $imageData     = file_get_contents($file->getPathname());
                    $base64Image   = base64_encode($imageData);
                    $imageMimeType = $file->getMimeType();

                    $client = OpenAI::client($_ENV['OPENAI_API_KEY'] ?? null);
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
        'size': 'Taille suggérée (optionnel)'
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
                        'max_tokens'  => 700,
                        'temperature' => 0.6,
                    ]);

                    // Nettoyage et décodage de la réponse
                    $jsonResponse = $response->choices[0]->message->content ?? '[]';
                    $jsonResponse = trim($jsonResponse);
                    $jsonResponse = preg_replace('/^```json\s*|\s*```$/', '', $jsonResponse);
                    preg_match('/\[.*\]/s', $jsonResponse, $matches);
                    $jsonResponse = $matches[0] ?? '[]';

                    $analysis = json_decode($jsonResponse, true);
                    if (!$analysis || !is_array($analysis) || empty($analysis)) {
                        file_put_contents('logs/openai_response.log', "Réponse brute : " . $jsonResponse . "\n", FILE_APPEND);
                        $error = 'Erreur lors du traitement des données. Vérifie le log openai_response.log.';
                    } else {
                        // Stocker l'analyse en session pour la réutiliser lors de la soumission du formulaire global
                        $session->set('analysis', $analysis);
                    }
                } catch (\Exception $e) {
                    $error = 'Erreur lors de l\'analyse de l\'image : ' . $e->getMessage();
                }
            } else {
                $error = "Veuillez télécharger une image valide.";
            }
        }

        // Si une analyse est présente en session, on reconstruit le formulaire global
        if ($session->has('analysis')) {
            $analysis = $session->get('analysis');
            $itemsData = [];


            foreach ($analysis as $clothingData) {
                // Création et pré-remplissage de l'entité ClothingItem
                $clothingItem = new ClothingItem();
                $clothingItem->setName($clothingData['name'] ?? '');
                $clothingItem->setBrand($clothingData['brand'] ?? '');
                $clothingItem->setColor($clothingData['color'] ?? '');
                $clothingItem->setPrice($clothingData['price'] ?? 0);
                $clothingItem->setDescription($clothingData['description'] ?? '');



                if (isset($clothingData['category'])) {
                    $category = $entityManager->getRepository(CategoryItem::class)
                        ->findOneBy(['name' => $clothingData['category']]);
                    if ($category) {
                        $clothingItem->setCategory($category);
                    }
                }

                // Création de l'entité OutfitItem associée à l'Outfit courant
                $outfitItem = new OutfitItem();
                $outfitItem->addOutfit($outfit);
                $outfitItem->setSize($clothingData['size'] ?? 'sm');
                $outfitItem->setWardrobe($outfit->getWardrobe());
                $outfitItem->setClothingItem($clothingItem);

                // Regrouper les deux entités dans un tableau
                $itemsData[] = [
                    'clothingItem' => $clothingItem,
                    'outfitItem'   => $outfitItem,
                ];
            }

            // Construction du formulaire global avec CollectionType
            $globalForm = $this->createFormBuilder(['items' => $itemsData])
                ->add('items', CollectionType::class, [
                    'entry_type'    => ClothingOutfitItemType::class,
                    'entry_options' => [],
                    'allow_add'     => true,
                    'by_reference'  => false,
                ])
                ->getForm();

            $globalForm->handleRequest($request);

            // Si le formulaire global est soumis et valide, persister les objets en BDD
            // Après le handleRequest()
            // Après l'appel à handleRequest()
            if ($globalForm->isSubmitted() && $globalForm->isValid()) {
                $data = $globalForm->getData();
                foreach ($globalForm->get('items') as $itemForm) {
                    // Récupérer l'image depuis le sous-formulaire du ClothingItem
                    $uploadedImage = $itemForm->get('clothingItem')->get('image')->getData();

                    $itemData = $itemForm->getData();
                    $newClothing   = $itemData['clothingItem'];
                    $newOutfitItem = $itemData['outfitItem'];

                    if ($uploadedImage) {
                        $newFilename = uniqid() . '.' . $uploadedImage->guessExtension();
                        try {
                            $uploadedImage->move(
                                $this->getParameter('upload_directory'),
                                $newFilename
                            );
                            $newClothing->addImage($newFilename);
                        } catch (FileException $e) {
                            // Gérez l'exception
                        }
                    }

                    $entityManager->persist($newClothing);
                    $entityManager->persist($newOutfitItem);
                }
                $entityManager->flush();
                // Supprimer les données de session pour éviter de recréer le formulaire
                $session->remove('analysis');
                return $this->redirectToRoute('analyze_image', ['id' => $outfit->getId()]);
            }


        }

        return $this->render('analysis/analyze.html.twig', [
            'outfit'      => $outfit,
            'imageForm'   => $imageForm->createView(),
            'globalForm'  => $globalForm ? $globalForm->createView() : null,
            'error'       => $error,
            'analysis'    => $analysis,
        ]);
    }
}
