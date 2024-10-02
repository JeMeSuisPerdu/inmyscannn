<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface; 
use App\Entity\Scan;
use App\Entity\Manga;

class HomeController extends AbstractController
{
    private $entityManager; 

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Récupérer tous les mangas avec leurs rôles
        $query = $this->entityManager->createQuery(
            'SELECT m, r
            FROM App\Entity\Manga m
            LEFT JOIN m.roles r
            ORDER BY m.titre ASC'
        )
        ->setMaxResults(100);
        
        $mangas = $query->getResult();
        
        // Préparer les rôles pour chaque manga
        $mangasRolesArray = [];
        foreach ($mangas as $manga) {
            $rolesForManga = [];
            foreach ($manga->getRoles() as $role) {
                $rolesForManga[] = $role->getNom();
            }
            $mangasRolesArray[$manga->getTitre()] = $rolesForManga;
        }

        // Récupérer les scans
        $nomMangas = [
            'blue lock', 'solo leveling', 'haikyuu', 'jujutsu kaisen',
            'one piece', 'vagabond', 'one punch man', 'dr stone',
            'kaiju n°8', 'domestic girlfriend', 'tsue to tsurugi no wistoria',
            'chainsaw man'
        ];
    
        $scans = $this->entityManager->getRepository(Scan::class)->findBy(['nom' => $nomMangas], null, 12);
        
// Nouvelle requête pour récupérer 15 mangas avec le rôle "Romance"
$romanceMangasQuery = $this->entityManager->createQuery(
    'SELECT m, r
    FROM App\Entity\Manga m
    JOIN m.roles r
    WHERE r.nom = :roleName
    ORDER BY m.titre ASC'
)
->setParameter('roleName', 'Romance')
->setMaxResults(29);
        
        $romanceMangas = $romanceMangasQuery->getResult();

// Nouvelle requête pour récupérer 19 mangas avec le rôle "Horreur"
$actionMangasQuery = $this->entityManager->createQuery(
    'SELECT m, r
    FROM App\Entity\Manga m
    JOIN m.roles r
    WHERE r.nom = :roleName
    ORDER BY m.titre ASC'
)
->setParameter('roleName', 'Horreur')
->setMaxResults(19);
        
        $actionMangas = $actionMangasQuery->getResult();

        return $this->render('home/home.html.twig', [
            'scans' => $scans,
            'mangas' => $mangas,
            'mangasWithRoles' => $mangasRolesArray,
            'actionMangas' => $actionMangas,
            'romanceMangas' => $romanceMangas,
        ]);
    }
}
