<?php

namespace App\Controller;

use OpenAI;
use App\Form\ImageUploadType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ImageAnalysisController extends AbstractController
{
    #[Route('/analyze-image', name: 'analyze_image', methods: ['GET', 'POST'])]
    public function analyzeImage(Request $request): Response
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
                    // Convertir l'image en base64
                    $imageData = file_get_contents($file->getPathname());
                    $base64Image = base64_encode($imageData);
                    $imageMimeType = $file->getMimeType(); // Type MIME

                    // Initialisation du client OpenAI
                    $client = OpenAI::client($apiKey);

                    // Envoi de la requête à GPT-4o en structurant correctement le message
                    $response = $client->chat()->create([
                        'model' => 'gpt-4o', // Modèle le plus récent compatible avec les images
                        'messages' => [
                            [
                                "role" => "system",
                                "content" => "Tu es un assistant spécialisé en analyse de mode et de vêtements.",
                            ],
                            [
                                "role" => "user",
                                "content" => [
                                    ["type" => "text", "text" => "Décris précisément les vêtements visibles sur cette image : type (t-shirt, pantalon, veste...), couleur et style. Et trouve la marque"],
                                    ["type" => "image_url", "image_url" => [
                                        "url" => "data:$imageMimeType;base64,$base64Image",
                                    ]],
                                ],
                            ],
                        ],
                        'max_tokens' => 300,
                        'temperature' => 0.5,
                    ]);

                    // Récupération du texte généré par OpenAI
                    $analysis = $response->choices[0]->message->content ?? 'Aucune description disponible.';
                } catch (\Exception $e) {
                    $error = 'Erreur lors de l\'analyse de l\'image : ' . $e->getMessage();
                }
            } else {
                $error = "Veuillez télécharger une image valide.";
            }
        }

        return $this->render('image_analysis/index.html.twig', [
            'form' => $form->createView(),
            'analysis' => $analysis,
            'error' => $error,
        ]);
    }
}
