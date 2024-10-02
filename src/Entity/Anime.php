<?php

namespace App\Entity;

use App\Repository\AnimeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: AnimeRepository::class)]
class Anime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $saison_numero = null;

    #[ORM\Column]
    private ?int $episode_number = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $lecteur_links = null;

    #[ORM\ManyToOne(inversedBy: 'episodes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Manga $manga_id = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false], nullable: true)]
    private ?bool $film = false; // Ajout du champ film

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSaisonNumero(): ?int
    {
        return $this->saison_numero;
    }

    public function setSaisonNumero(int $saison_numero): static
    {
        $this->saison_numero = $saison_numero;

        return $this;
    }

    public function getEpisodeNumber(): ?int
    {
        return $this->episode_number;
    }

    public function setEpisodeNumber(int $episode_number): static
    {
        $this->episode_number = $episode_number;

        return $this;
    }

    public function getLecteurLinks(): ?array
    {
        return $this->lecteur_links;
    }

    public function setLecteurLinks(?array $lecteur_links): static
    {
        $this->lecteur_links = $lecteur_links;

        return $this;
    }

    public function addLecteurLink(string $lecteur, string $url): static
    {
        if (!$this->lecteur_links) {
            $this->lecteur_links = [];
        }
        
        $this->lecteur_links[$lecteur] = $url;

        return $this;
    }

    public function removeLecteurLink(string $lecteur): static
    {
        if (isset($this->lecteur_links[$lecteur])) {
            unset($this->lecteur_links[$lecteur]);
        }

        return $this;
    }

    public function getMangaId(): ?Manga
    {
        return $this->manga_id;
    }

    public function setMangaId(?Manga $manga_id): static
    {
        $this->manga_id = $manga_id;

        return $this;
    }

    public function getFilm(): ?bool
    {
        return $this->film;
    }

    public function setFilm(?bool $film): static
    {
        $this->film = $film;

        return $this;
    }
}
