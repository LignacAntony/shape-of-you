<?php

namespace App\Controller;

use App\Entity\Outfit;
use App\Form\OutfitIaType;
use App\Repository\WardrobeRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenAI;
use App\Form\ImageUploadType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ImageAnalysisController extends AbstractController
{
    #[Route('/analyze-image', name: 'analyze_image', methods: ['GET', 'POST'])]
    public function analyzeImage(Request $request, EntityManagerInterface $em, WardrobeRepository $wardrobeRepo): Response
    {
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? null;
        if (!$apiKey) {
            throw new \RuntimeException('La clé API OpenAI est manquante.');
        }

        $form = $this->createForm(ImageUploadType::class);
        $form->handleRequest($request);

        $analysis = null;
        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();

            if ($file) {
                try {
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
                                    ["type" => "text", "text" => "Analyse l'image et retourne un **JSON strictement valide**, sous la forme d'un **tableau** (array) si plusieurs vêtements sont détectés.
                Chaque vêtement doit être un objet JSON contenant :
                [
                    {
                        'nom': 'Nom du vêtement identifiable (ex: T-shirt Adidas Classic)',
                        'categorie': 'Une des catégories suivantes : [T-shirts, Chemises, Débardeurs, Pulls, Robes, Manteaux, Vestes, Pantalons, Shorts, Jupes, Chaussures, Bottes, Sandales, Accessoires, Ceintures, Écharpes, Maillots de bain, Lingerie, Pyjamas]',
                        'description': 'Une brève description du vêtement, son style, sa coupe et ses matériaux',
                        'marque': 'Si une marque est identifiable, sinon null',
                        'couleur_hex': 'Code couleur en format HEXA (ex: #FF5733)',
                        'prix': 'Prix estimé du vêtement en euros (ex: 49.99)',
                        'site': 'URL du site où acheter le vêtement (tu peux t'aider de la marque), sinon null'
                    }
                ]
                Réponds **UNIQUEMENT** avec un tableau JSON valide et rien d'autre. Ne mets aucun texte avant ou après."],
                                    ["type" => "image_url", "image_url" => [
                                        "url" => "data:$imageMimeType;base64,$base64Image",
                                    ]],
                                ],
                            ],
                        ],
                        'max_tokens' => 700,
                        'temperature' => 0.5,
                    ]);


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

        $outfit = new Outfit();

        $outfitForm = $this->createForm(OutfitIaType::class, $outfit);
        $outfitForm->handleRequest($request);


        if ($outfitForm->isSubmitted() && $outfitForm->isValid()) {
            $outfit->setAuthor($this->getUser());

            $em->persist($outfit);
            $em->flush();

            $this->addFlash('success', 'Nouvel outfit créé avec succès !');
            return $this->redirectToRoute('analyze_image');
        }

        return $this->render('image_analysis/index.html.twig', [
            'form' => $outfitForm->createView(),
            'analysis' => $analysis,
            'error' => $error,
        ]);
    }
}
