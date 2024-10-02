<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpClient\HttpClient;
use ZipArchive;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DownloadController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/download/chapter/{chapter}', name: 'download_chapter')]
    public function downloadChapter(int $chapter): Response
    {
        $baseUrl = 'https://anime-sama.fr/s2/scans/Ao%20Ashi/';
        $maxPages = 30; // Ajustez en fonction du nombre maximum de pages par chapitre
        $client = HttpClient::create();

        // Créer un fichier ZIP
        $zip = new ZipArchive();
        $zipFileName = 'chapter_' . $chapter . '.zip';
        $zipFilePath = tempnam(sys_get_temp_dir(), $zipFileName);

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return new Response('Impossible d\'ouvrir le fichier ZIP', 500);
        }

        // Boucler sur chaque page pour ajouter les images au ZIP
        for ($page = 1; $page <= $maxPages; $page++) {
            $imgUrl = $baseUrl . $chapter . '/' . $page . '.jpg';
            $response = $client->request('GET', $imgUrl);

            // Vérifier si l'image existe
            if ($response->getStatusCode() === 200) {
                // Ajouter l'image au ZIP
                $zip->addFromString('page_' . $page . '.jpg', $response->getContent());
            } else {
                // Si l'image n'existe pas, on sort de la boucle
                break;
            }
        }

        // Fermer le fichier ZIP
        $zip->close();

        // Créer une réponse pour le téléchargement du ZIP
        $response = new StreamedResponse(function () use ($zipFilePath) {
            readfile($zipFilePath);
            unlink($zipFilePath); // Supprimer le fichier temporaire après l'envoi
        });

        // Configurer les en-têtes pour forcer le téléchargement
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $zipFileName . '"');

        return $response;
    }
    
}
