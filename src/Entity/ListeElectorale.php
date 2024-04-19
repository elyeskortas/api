<?php

namespace App\Entity;

use App\Repository\ListeElectoraleRepository;
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

    #[ORM\Column(length: 255)]
    private ?string $Date_création = null;

    #[ORM\OneToMany(targetEntity: Candidat::class, mappedBy: 'listeElectorale')]
    private Collection $candidats;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(string $Type): static
    {
        $this->Type = $Type;

        return $this;
    }

    public function getDateCréation(): ?string
    {
        return $this->Date_création;
    }

    public function setDateCréation(string $Date_création): static
    {
        $this->Date_création = $Date_création;

        return $this;
    }
}
