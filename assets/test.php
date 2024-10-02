<?php
// Configuration de la connexion à la base de données
$host = 'localhost'; // Remplace par ton hôte
$db = 'inmyscan'; // Remplace par le nom de ta base
$user = 'root'; // Remplace par ton utilisateur
$pass = ''; // Remplace par ton mot de passe

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}

// Liens des épisodes
$eps1_reader1 = [
    'https://video.sibnet.ru/shell.php?videoid=4667514',
    'https://video.sibnet.ru/shell.php?videoid=4667523',
    'https://video.sibnet.ru/shell.php?videoid=4667532',
    'https://video.sibnet.ru/shell.php?videoid=4667548',
    'https://video.sibnet.ru/shell.php?videoid=4667557',
    'https://video.sibnet.ru/shell.php?videoid=4667566',
    'https://video.sibnet.ru/shell.php?videoid=4667578',
    'https://video.sibnet.ru/shell.php?videoid=4667599',
    'https://video.sibnet.ru/shell.php?videoid=4667621',
    'https://video.sibnet.ru/shell.php?videoid=4667634',
    'https://video.sibnet.ru/shell.php?videoid=4667642',
    'https://video.sibnet.ru/shell.php?videoid=4667648',
    'https://video.sibnet.ru/shell.php?videoid=4667656',
    'https://video.sibnet.ru/shell.php?videoid=4667663',
    'https://video.sibnet.ru/shell.php?videoid=4667667',
    'https://video.sibnet.ru/shell.php?videoid=4667673',
    'https://video.sibnet.ru/shell.php?videoid=4667683',
    'https://video.sibnet.ru/shell.php?videoid=4667689',
    'https://video.sibnet.ru/shell.php?videoid=4667696',
    'https://video.sibnet.ru/shell.php?videoid=4667717',
    'https://video.sibnet.ru/shell.php?videoid=4667725',
    'https://video.sibnet.ru/shell.php?videoid=4667735',
    'https://video.sibnet.ru/shell.php?videoid=4667746',
    'https://video.sibnet.ru/shell.php?videoid=4667756',
    // ... autres liens
];

$eps1_reader2 = [
    'https://sendvid.com/embed/g6zmqnsf',
    'https://sendvid.com/embed/qg2vbfam',
    'https://sendvid.com/embed/76wtzil1',
    'https://sendvid.com/embed/2q1y8ec7',
    'https://sendvid.com/embed/dlz7nl4n',
    'https://sendvid.com/embed/mj83fr9m',
    'https://sendvid.com/embed/otsog419',
    'https://sendvid.com/embed/3ifovb3e',
    'https://sendvid.com/embed/4i4curvz',
    'https://sendvid.com/embed/30lbi2fl',
    'https://sendvid.com/embed/f3071f29',
    'https://sendvid.com/embed/fqjgqgzr',
    'https://sendvid.com/embed/6zzzue8o',
    'https://sendvid.com/embed/cdlh6awa',
    'https://sendvid.com/embed/6uam2dad',
    'https://sendvid.com/embed/bryqucpz',
    'https://sendvid.com/embed/msul45pc',
    'https://sendvid.com/embed/252sx7a9',
    'https://sendvid.com/embed/2qtjrd0w',
    'https://sendvid.com/embed/pxfxne05',
    'https://sendvid.com/embed/lm8vbajm',
    'https://sendvid.com/embed/qxa3u3tu',
    'https://sendvid.com/embed/zbwl4seu',
    'https://sendvid.com/embed/pgrewgwq',
];

$eps1_reader3 = [
    'https://vidmoly.to/embed-miq7pn6rtrdg.html',
    'https://vidmoly.to/embed-obrqlw8x5ywz.html',
    'https://vidmoly.to/embed-wcxlfi9wz4iu.html',
    'https://vidmoly.to/embed-zgcuxwkpq9iu.html',
    'https://vidmoly.to/embed-satjrc3zbb4t.html',
    'https://vidmoly.to/embed-oa9d567vhr2z.html',
    'https://vidmoly.to/embed-rt1ob6v46tin.html',
    'https://vidmoly.to/embed-ce3u1hjjqaq4.html',
    'https://vidmoly.to/embed-xglidp0obm9q.html',
    'https://vidmoly.to/embed-f0el3lcc8v01.html',
    'https://vidmoly.to/embed-pgjec874nk4k.html',
    'https://vidmoly.to/embed-j5t68zyr0ikv.html',
    'https://vidmoly.to/embed-pli0l1j432hv.html',
    'https://vidmoly.to/embed-a0pice8bx4ir.html',
    'https://vidmoly.to/embed-5zrl3bw2usmn.html',
    'https://vidmoly.to/embed-yl0z6x6eaobe.html',
    'https://vidmoly.to/embed-07kf83jz5y5a.html',
    'https://vidmoly.to/embed-7yobcauhatpn.html',
    'https://vidmoly.to/embed-huqss4je1mj8.html',
    'https://vidmoly.to/embed-9h3cm7pv4n24.html',
    'https://vidmoly.to/embed-9sow1oh8h7b2.html',
    'https://vidmoly.to/embed-gc1uha670zzt.html',
    'https://vidmoly.to/embed-5zc4vofcqbov.html',
    'https://vidmoly.to/embed-balncwsy88ch.html',
    // ... autres liens
];

// Insertion des liens dans la base de données en parcourant tous les tableaux
$maxEpisodes = max(count($eps1_reader1), count($eps1_reader2), count($eps1_reader3)); // Nombre maximum d'épisodes

for ($episodeNumber = 1; $episodeNumber <= $maxEpisodes; $episodeNumber++) {
    // Initialiser un tableau pour les liens de l'épisode
    $links = [];
    
    // Vérifier si le lien existe pour chaque lecteur
    if (isset($eps1_reader1[$episodeNumber - 1])) {
        $links['reader1'] = $eps1_reader1[$episodeNumber - 1]; // Récupérer le lien pour le lecteur 1
    }
    
    if (isset($eps1_reader2[$episodeNumber - 1])) {
        $links['reader2'] = $eps1_reader2[$episodeNumber - 1]; // Récupérer le lien pour le lecteur 2
    }
    
    if (isset($eps1_reader3[$episodeNumber - 1])) {
        $links['reader3'] = $eps1_reader3[$episodeNumber - 1]; // Récupérer le lien pour le lecteur 3
    }
    
    // Convertir les liens en format JSON pour l'insertion
    $jsonLinks = json_encode($links);
    
    // Insertion des liens dans la base de données
    $stmt = $pdo->prepare("INSERT INTO anime (manga_id_id, saison_numero, episode_number, lecteur_links) VALUES (:manga_id_id, :saison_numero, :episode_number, :lecteur_links)");
    $stmt->execute([
        ':manga_id_id' => 172, // Remplace par l'ID de l'anime
        ':saison_numero' => 1, // Remplace par le numéro de saison
        ':episode_number' => $episodeNumber, // Utilise le numéro d'épisode actuel
        ':lecteur_links' => $jsonLinks // Insère les liens sous forme de JSON
    ]);
}

echo "Liens insérés avec succès.";
