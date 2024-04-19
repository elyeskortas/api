<?php

namespace App\Entity;

use App\Repository\ResultatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultatRepository::class)]
class Resultat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Candidat = null;

    #[ORM\Column(length: 255)]
    private ?string $Total_de_votes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $Id): static
    {
        $this->Id = $Id;

        return $this;
    }

    public function getCandidat(): ?string
    {
        return $this->Candidat;
    }

    public function setCandidat(string $Candidat): static
    {
        $this->Candidat = $Candidat;

        return $this;
    }

    public function getTotalDeVotes(): ?string
    {
        return $this->Total_de_votes;
    }

    public function setTotalDeVotes(string $Total_de_votes): static
    {
        $this->Total_de_votes = $Total_de_votes;

        return $this;
    }
}
