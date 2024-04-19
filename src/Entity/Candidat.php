<?php

namespace App\Entity;

use App\Repository\CandidatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidatRepository::class)]
class Candidat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Prénom = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    #[ORM\Column(length: 255)]
    private ?string $Date_de_naissance = null;

    #[ORM\Column(length: 255)]
    private ?string $Email = null;

    #[ORM\ManyToOne(targetEntity: ListeElectorale::class, inversedBy: "candidats")]
    private ?ListeElectorale $listeElectorale = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrénom(): ?string
    {
        return $this->Prénom;
    }

    public function setPrénom(string $Prénom): static
    {
        $this->Prénom = $Prénom;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;

        return $this;
    }

    public function getDateDeNaissance(): ?string
    {
        return $this->Date_de_naissance;
    }

    public function setDateDeNaissance(string $Date_de_naissance): static
    {
        $this->Date_de_naissance = $Date_de_naissance;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): static
    {
        $this->Email = $Email;

        return $this;
    }
}
