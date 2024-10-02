<?php

namespace App\Entity;

use App\Repository\MangaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MangaRepository::class)]
class Manga
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $author = null;

    /**
     * @var Collection<int, Anime>
     */
    #[ORM\OneToMany(targetEntity: Anime::class, mappedBy: 'manga_id', orphanRemoval: true)]
    private Collection $episodes;

    /**
     * @var Collection<int, Scan>
     */
    #[ORM\ManyToMany(targetEntity: Scan::class, mappedBy: 'manga_id')]
    private Collection $scans;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'mangas')]
    #[ORM\JoinTable(name: 'role_manga')]
    private Collection $roles;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
        $this->scans = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Anime>
     */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    public function addEpisode(Anime $episode): static
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes->add($episode);
            $episode->setMangaId($this);
        }

        return $this;
    }

    public function removeEpisode(Anime $episode): static
    {
        if ($this->episodes->removeElement($episode)) {
            // set the owning side to null (unless already changed)
            if ($episode->getMangaId() === $this) {
                $episode->setMangaId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Scan>
     */
    public function getScans(): Collection
    {
        return $this->scans;
    }

    public function addScan(Scan $scan): static
    {
        if (!$this->scans->contains($scan)) {
            $this->scans->add($scan);
            $scan->addMangaId($this);
        }

        return $this;
    }

    public function removeScan(Scan $scan): static
    {
        if ($this->scans->removeElement($scan)) {
            $scan->removeMangaId($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            $role->addManga($this);
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        if ($this->roles->removeElement($role)) {
            $role->removeManga($this);
        }

        return $this;
    }
}
