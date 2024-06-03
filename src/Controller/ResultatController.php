<?php

namespace App\Controller;

use App\Entity\Resultat;
use App\Entity\Candidat;
use App\Repository\VoteRepository;
use App\Repository\CandidatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResultatController extends AbstractController
{
    private $entityManager;
    private $voteRepository;
    private $candidatRepository;

    public function __construct(EntityManagerInterface $entityManager, VoteRepository $voteRepository, CandidatRepository $candidatRepository)
    {
        $this->entityManager = $entityManager;
        $this->voteRepository = $voteRepository;
        $this->candidatRepository = $candidatRepository;
    }

    #[Route('/api/resultats', name: 'resultats')]
public function index(): JsonResponse
{
    // Récupérer les résultats depuis la base de données
    $resultats = $this->entityManager->getRepository(Resultat::class)->findAll();

    // Convertir les résultats en format JSON
    $data = [];
    foreach ($resultats as $resultat) {
        $data[] = [
            'id' => $resultat->getId(),
            'candidat' => $resultat->getCandidat()->getNom(), // Ou toute autre propriété du candidat
            'total_votes' => $resultat->getTotalVotes(),
        ];
    }

    // Retourner les résultats sous forme de réponse JSON
    return new JsonResponse($data);
}

    #[Route('/api/resultats/calculate', name: 'resultats_calculate')]
    public function calculate(): Response
    {
        // Récupérer tous les votes depuis le référentiel VoteRepository
        $votes = $this->voteRepository->findAll();

        // Initialiser un tableau pour stocker le nombre de votes par candidat
        $votesParCandidat = [];

        // Calculer le nombre total de votes pour chaque candidat
        foreach ($votes as $vote) {
            $candidatId = $vote->getCandidat()->getId();
            if (!isset($votesParCandidat[$candidatId])) {
                $votesParCandidat[$candidatId] = 0;
            }
            $votesParCandidat[$candidatId]++;
        }

        // Mettre à jour les résultats enregistrés dans la base de données
        foreach ($votesParCandidat as $candidatId => $totalVotes) {
            $candidat = $this->candidatRepository->find($candidatId); // Utilisez le référentiel CandidatRepository pour obtenir le candidat
            $resultat = $this->entityManager->getRepository(Resultat::class)->findOneBy(['candidat' => $candidat]);
            if (!$resultat) {
                // Si le résultat n'existe pas, créez une nouvelle instance de Resultat
                $resultat = new Resultat();
                $resultat->setCandidat($candidat);
            }
            $resultat->setTotalVotes($totalVotes);
            $this->entityManager->persist($resultat);
        }

        // Enregistrer les modifications dans la base de données
        $this->entityManager->flush();

        // Rediriger vers la page des résultats
        return $this->redirectToRoute('resultats');
    }
}
