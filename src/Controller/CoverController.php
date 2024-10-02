<?php

namespace App\Controller;

use App\Entity\Anime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Manga;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CoverController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/manga/scan/{name}/{chapter<\d+>}/{page<\d+>}', name: 'chapter_cover')]
    public function getCover(string $name, int $chapter, int $page): Response
    {
        $baseUrl = 'https://anime-sama.fr/s2/scans/';
        
        // Construction de l'URL à partir des paramètres
        $coverUrl = $baseUrl . $name . '/' . $chapter . '/' . $page . '.jpg';
        
        $client = HttpClient::create();
        $response = $client->request('GET', $coverUrl);
    
        if ($response->getStatusCode() === 200) {
            return $this->render('scan_view/scan.html.twig', [
                'coverImage' => $coverUrl,
                'chapter' => $chapter,
                'page' => $page,
                'name' => $name,
            ]);
        } elseif ($response->getStatusCode() === 404) {
            // Essayer de rediriger vers le chapitre suivant
            $nextChapter = $chapter + 1;
            $nextPage = 1;
            return $this->redirectToRoute('chapter_cover', [
                'name' => $name,
                'chapter' => $nextChapter,
                'page' => $nextPage,
            ]);
        }
    
        throw $this->createNotFoundException('Image non trouvée');
    }

    #[Route('/manga/search', name: 'search_manga')]
    public function searchManga(Request $request, EntityManagerInterface $entityManager): Response
    {
        $searchTerm = strtolower($request->query->get('query')); 
        $manga = $entityManager->getRepository(Manga::class)->findOneBy(['titre' => $searchTerm]);
    
        if ($manga) {
            // Rediriger vers la route 'manga_profile' avec l'ID du manga
            return $this->redirectToRoute('manga_profile', [
                'id' => $manga->getId(), // Passe l'ID du manga
            ]);
        }
    
        // Gérer le cas où le manga n'est pas trouvé
        return $this->redirectToRoute('app_home'); // Redirection si aucun résultat
    }
    #[Route('/manga/search/ajax', name: 'ajax_search_manga')]
    public function ajaxSearchManga(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $searchTerm = strtolower($request->query->get('query'));
    
        // Utiliser une requête personnalisée pour récupérer les 10 premiers résultats où le terme existe n'importe où dans le titre
        $mangas = $entityManager->getRepository(Manga::class)
            ->createQueryBuilder('m')
            ->where('LOWER(m.titre) LIKE :term')
            ->setParameter('term', '%' . $searchTerm . '%') // Le '%' permet de trouver le terme n'importe où dans le titre
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    
        // Préparer les résultats pour le format JSON
        $results = [];
        foreach ($mangas as $manga) {
            $results[] = [
                'id' => $manga->getId(),
                'titre' => $manga->getTitre(), // Titre du manga
            ];
        }
    
        return $this->json($results);
    }

    #[Route('/manga/{id}', name: 'manga_profile')]
    public function mangaProfile(int $id): Response
    {
        // Récupérer le manga par ID
        $manga = $this->entityManager->getRepository(Manga::class)->find($id);
    
        if (!$manga) {
            throw $this->createNotFoundException('Manga non trouvé');
        }
        
        // Récupérer les scans associés
        $scans = $manga->getScans();
        
        // Récupérer les saisons d'anime associées au manga
        $saisons = $this->entityManager->getRepository(Anime::class)->findBy(['manga_id' => $manga], ['saison_numero' => 'ASC']);
    
        // Grouper les épisodes par saison
        $groupedEpisodesBySaison = [];
        foreach ($saisons as $anime) {
            $groupedEpisodesBySaison[$anime->getSaisonNumero()][] = $anime;
        }
        
        // Récupérer les rôles associés au manga
        $rolesForManga = [];
        foreach ($manga->getRoles() as $role) {
            $rolesForManga[] = $role->getNom();
        }
    
        return $this->render('manga/profile.html.twig', [
            'manga' => $manga,
            'scans' => $scans,
            'saisons' => $groupedEpisodesBySaison,
            'roles' => $rolesForManga, // Passer les rôles au template
        ]);
    }

#[Route('/manga/{id}/scans', name: 'manga_scans')]
public function mangaScans(int $id): Response
{
    $manga = $this->entityManager->getRepository(Manga::class)->find($id);

    if (!$manga) {
        throw $this->createNotFoundException('Manga non trouvé');
    }

    // Récupérer le premier scan
    $scans = $manga->getScans();
    if ($scans->isEmpty()) {
        throw $this->createNotFoundException('Aucun scan trouvé pour ce manga');
    }

    $firstScan = $scans->first();

    // Extraire le lien complet du scan
    $lien = $firstScan->getLien(); // Exemple : "https://anime-sama.fr/s2/scans/All%20You%20Need%20Is%20Kill/1/1.jpg"

    // Découper l'URL pour extraire les parties nécessaires
    $parts = explode('/', parse_url($lien, PHP_URL_PATH)); 
    $nomManga = $parts[3]; // Position 3 correspond au nom du manga

    // Rediriger vers chapter_cover avec les informations extraites
    return $this->redirectToRoute('chapter_cover', [
        'name' => $nomManga, // Utilise le nom extrait de l'URL
        'chapter' => 1, // ou le chapitre souhaité
        'page' => 1 // ou la page souhaitée
    ]);
}

#[Route('/anime/{mangaId}/{saison}', name: 'voir_anime')]
public function voirAnime(int $mangaId, int $saison, EntityManagerInterface $entityManager): Response
{
    $manga = $entityManager->getRepository(Manga::class)->find($mangaId);

    if (!$manga) {
        throw $this->createNotFoundException('Manga non trouvé.');
    }

    // Récupérer les épisodes pour la saison spécifiée
    $episodes = $entityManager->getRepository(Anime::class)->findBy([
        'manga_id' => $manga,
        'saison_numero' => $saison
    ]);

    $episodeLinks = [];
    foreach ($episodes as $episode) {
        $lecteurLinks = $episode->getLecteurLinks();
        $episodeLinks[$episode->getEpisodeNumber()] = $lecteurLinks;
    }

    return $this->render('anime/voir_anime.html.twig', [
        'manga' => $manga,
        'episodes' => $episodeLinks,
        'saison' => $saison,
    ]);
}


}
