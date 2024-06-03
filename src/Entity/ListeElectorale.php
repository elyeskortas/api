<?php

namespace App\Entity;

use App\Repository\ListeElectoraleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListeElectoraleRepository::class)]
class ListeElectorale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $Type = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $Date_création = null;

    #[ORM\OneToMany(targetEntity: Candidat::class, mappedBy: 'listeElectorale')]
    private Collection $candidats;

    #[ORM\OneToMany(targetEntity: Electeur::class, mappedBy: 'listeElectorale')]
    private Collection $electeurs;

    public function __construct()
    {
        $this->candidats = new ArrayCollection();
        $this->electeurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(string $Type): self
    {
        $this->Type = $Type;
        return $this;
    }

    public function getDateCréation(): ?\DateTimeInterface
    {
        return $this->Date_création;
    }

    public function setDateCréation(\DateTimeInterface $Date_création): self
    {
        $this->Date_création = $Date_création;
        return $this;
    }

    /**
     * @return Collection|Candidat[]
     */
    public function getCandidats(): Collection
    {
        return $this->candidats;
    }

    /**
     * @return Collection|Electeur[]
     */
    public function getElecteurs(): Collection
    {
        return $this->electeurs;
    }
}
