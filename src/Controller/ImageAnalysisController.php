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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageAnalysisController extends AbstractController
{
    #[Route('/outfit/{id}/analyze', name: 'analyze_image', methods: ['GET', 'POST'])]
    public function analyzeImage(Outfit $outfit, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user !== $outfit->getAuthor()) {
            return $this->redirectToRoute('app_home');
        }


        $session = $request->getSession();

        if ($request->isMethod('GET')) {
            $session->remove('analysis');
        }

        $imageForm = $this->createForm(ImageUploadType::class);
        $imageForm->handleRequest($request);

        $analysis = [];
        $error = null;
        $globalForm = null;

        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            $file = $imageForm->get('image')->getData();

            if ($file) {
                try {
                    $imageData = file_get_contents($file->getPathname());
                    $sourceImage = imagecreatefromstring($imageData);
                    if (!$sourceImage) {
                        throw new \Exception("Impossible de créer l'image depuis les données.");
                    }

                    $width = imagesx($sourceImage);
                    $height = imagesy($sourceImage);

                    $newWidth = 800;
                    $newHeight = intval($height * ($newWidth / $width));

                    $newImage = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                    ob_start();
                    if ($file->getMimeType() === 'image/png') {
                        imagepng($newImage);
                    } else {
                        imagejpeg($newImage, null, 90); // 90 correspond à la qualité JPEG
                    }
                    $resizedImageData = ob_get_clean();

                    imagedestroy($sourceImage);
                    imagedestroy($newImage);

                    $base64Image = base64_encode($resizedImageData);
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
                                                    'price': 'Prix estimé du vêtement en euros (ex: 49.99, tu le multiplie par 100)',
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
                        'temperature' => 0.6,
                    ]);

                    $jsonResponse = $response->choices[0]->message->content ?? '[]';
                    $jsonResponse = trim($jsonResponse);
                    $jsonResponse = preg_replace('/^```json\s*|\s*```$/', '', $jsonResponse);
                    preg_match('/\[.*\]/s', $jsonResponse, $matches);
                    $jsonResponse = $matches[0] ?? '[]';

                    $analysis = json_decode($jsonResponse, true);
                    if (!is_array($analysis) || empty($analysis)) {
                        file_put_contents('logs/openai_response.log', "Réponse brute : " . $jsonResponse . "\n", FILE_APPEND);
                        $error = 'Erreur lors du traitement des données. Vérifie le log openai_response.log.';
                    } else {
                        $session->set('analysis', $analysis);
                        $this->addFlash('info', 'Analysis data stored in session.');

                    }
                } catch (\Exception $e) {
                    $error = 'Erreur lors de l\'analyse de l\'image : ' . $e->getMessage();
                }
            } else {
                $error = "Veuillez télécharger une image valide.";
            }
        }

        if ($session->has('analysis')) {
            $analysis = $session->get('analysis');
            $itemsData = [];


            foreach ($analysis as $clothingData) {
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

                $outfitItem = new OutfitItem();
                $outfitItem->addOutfit($outfit);
                $outfitItem->setSize($clothingData['size'] ?? 'sm');
                $outfitItem->setWardrobe($outfit->getWardrobe());
                $outfitItem->setClothingItem($clothingItem);

                $itemsData[] = [
                    'clothingItem' => $clothingItem,
                    'outfitItem' => $outfitItem,
                ];
            }

            $globalForm = $this->createFormBuilder(['items' => $itemsData])
                ->add('items', CollectionType::class, [
                    'entry_type' => ClothingOutfitItemType::class,
                    'entry_options' => [],
                    'allow_add' => true,
                    'by_reference' => false,
                ])
                ->getForm();

            $globalForm->handleRequest($request);


            if ($globalForm->isSubmitted() && $globalForm->isValid()) {
                $data = $globalForm->getData();
                foreach ($globalForm->get('items') as $itemForm) {
                    $uploadedImage = $itemForm->get('clothingItem')->get('image')->getData();

                    $itemData = $itemForm->getData();
                    $newClothing = $itemData['clothingItem'];
                    $newOutfitItem = $itemData['outfitItem'];

                    if ($uploadedImage) {
                        $newFilename = uniqid() . '.' . $uploadedImage->guessExtension();
                        try {
                            $uploadedImage->move(
                                $this->getParameter('upload_directory'),
                                $newFilename
                            );
                            $newClothing->addImage('uploads/images/' . $newFilename);
                        } catch (FileException $e) {
                        }
                    }

                    $entityManager->persist($newClothing);
                    $entityManager->persist($newOutfitItem);
                }
                $entityManager->flush();
                $session->remove('analysis');
                $this->addFlash('info', 'Analysis data removed from session.');

                $session->remove('globalForm');
                return $this->redirectToRoute('analyze_image', ['id' => $outfit->getId()]);
            }
        }

        return $this->render('analysis/analyze.html.twig', [
            'outfit' => $outfit,
            'imageForm' => $imageForm->createView(),
            'globalForm' => $globalForm ? $globalForm->createView() : null,
            'error' => $error,
            'analysis' => $analysis,
        ]);
    }
}
