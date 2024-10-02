<?php

namespace App\Controller;

use App\Entity\Anime;
use App\Entity\Manga;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AnimeRepository;
use Psr\Cache\CacheItemPoolInterface;
class MangaController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/catalogue/', name: 'catalogue')]
    public function catalogue(Request $request, CacheItemPoolInterface $cache): Response
    {
        $limit = 80; // Nombre de mangas par page
        $page = $request->query->getInt('page', 1); // Récupérer le numéro de page
        $offset = ($page - 1) * $limit;
    
        $roles = $request->query->get('roles');
        $searchTerm = $request->query->get('search', '');
    
        // Création d'une clé de cache unique
        $cacheKey = 'catalogue_' . md5(serialize([$roles, $searchTerm, $page]));
    
        // Essayer de récupérer les résultats du cache
        $cachedResult = $cache->getItem($cacheKey);
        
        if ($cachedResult->isHit()) {
            // Récupérer les données du cache
            $data = $cachedResult->get();
            return $this->render('catalogue/catalogue.html.twig', $data);
        }
    
        // Récupérer le total des mangas uniques avec les filtres
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('COUNT(DISTINCT m.id)')
            ->from('App\Entity\Manga', 'm')
            ->leftJoin('m.roles', 'r');
    
        if (!empty($roles)) {
            $queryBuilder->andWhere('r.nom IN (:roles)')
                ->setParameter('roles', $roles);
        }
    
        if (!empty($searchTerm)) {
            $queryBuilder->andWhere('m.titre LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }
    
        $totalMangas = $queryBuilder->getQuery()->getSingleScalarResult();
        $totalPages = ceil($totalMangas / $limit);
    
        // Récupérer les IDs des mangas uniques
        $mangasQuery = $this->entityManager->createQueryBuilder();
        $mangasQuery->select('DISTINCT m.id')
            ->from('App\Entity\Manga', 'm')
            ->leftJoin('m.roles', 'r');
    
        if (!empty($roles)) {
            $mangasQuery->andWhere('r.nom IN (:roles)')
                ->setParameter('roles', $roles);
        }
    
        if (!empty($searchTerm)) {
            $mangasQuery->andWhere('m.titre LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }
    
        $mangasQuery->orderBy('m.titre', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
    
        // Récupérer les IDs des mangas uniques
        $mangaIds = $mangasQuery->getQuery()->getArrayResult();
    
        // Récupérer les mangas avec leurs rôles en fonction des IDs récupérés
        $mangasWithRolesQuery = $this->entityManager->createQuery(
            'SELECT m, r
            FROM App\Entity\Manga m
            LEFT JOIN m.roles r
            WHERE m.id IN (:mangaIds)'
        )->setParameter('mangaIds', array_column($mangaIds, 'id'));
    
        // Récupérer les mangas avec leurs rôles
        $mangas = $mangasWithRolesQuery->getResult();
    
        // Préparer les rôles pour chaque manga
        $mangasRolesArray = [];
        foreach ($mangas as $manga) {
            $rolesForManga = [];
            foreach ($manga->getRoles() as $role) {
                $rolesForManga[] = $role->getNom();
            }
            $mangasRolesArray[$manga->getId()] = $rolesForManga;
        }
    
        // Récupérer les rôles affichés
        $rolesAffiches = ['Action', 'Yuri', 'Isekai', 'Drame', 'Comédie', 'Combats', 'Sports', 'Shônen', 'Surnaturel', 'Film', 'Anime', 'Scans'];
        $rolesQuery = $this->entityManager->createQuery(
            'SELECT r FROM App\Entity\Role r WHERE r.nom IN (:rolesAffiches)'
        )->setParameter('rolesAffiches', $rolesAffiches);
    
        $roles = $rolesQuery->getResult();
    
        // Sauvegarder les résultats dans le cache avec une durée de vie de 1 heure
        $cachedData = [
            'roles' => $roles,
            'mangas' => $mangas,
            'mangasWithRoles' => $mangasRolesArray,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ];
    
        $cachedResult->set($cachedData);
        $cachedResult->expiresAfter(3600); // 1 heure de durée de vie
        $cache->save($cachedResult);
    
        return $this->render('catalogue/catalogue.html.twig', $cachedData);
    }
    

    // //INSERTION DES PHOTOS DANS LA BDD POUR CHAQUE MANGA DE L ID 1 A 459 
    // #[IsGranted('ROLE_ADMIN')]
    // #[Route('/anime/boucle', name: 'vzoir_aenizzzme')]
    // public function updateMangaPhotos(): Response
    // {
    //     // Boucle de 1 à 459
    //     for ($i = 1; $i <= 1513; $i++) {
    //         // Récupérer l'entité Manga par son id
    //         $manga = $this->entityManager->getRepository(Manga::class)->find($i);
    
    //         // Vérifie si le manga existe
    //         if ($manga) {
    //             // Mettre à jour le champ photo
    //             $photoUrl = 'photo_' . $i . '.jpg'; 
    //             $manga->setPhoto($photoUrl);
    
    //             // Persist la mise à jour
    //             $this->entityManager->persist($manga);
    //         }
    //     }
    
    //     // Sauvegarde les changements dans la base de données
    //     $this->entityManager->flush();
    
    //     // Retourne une réponse simple
    //     return new Response('Mise à jour des photos terminée.');
    // }
}
