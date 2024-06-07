<?php

namespace App\Controller;

use App\Entity\Resultat;
use App\Repository\ResultatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResultatController extends AbstractController
{
    private $entityManager;
    private $resultatRepository;

    public function __construct(EntityManagerInterface $entityManager, ResultatRepository $resultatRepository)
    {
        $this->entityManager = $entityManager;
        $this->resultatRepository = $resultatRepository;
    }

    #[Route('/api/resultats', name: 'resultats')]
    public function index(): JsonResponse
    {
        $resultats = $this->entityManager->getRepository(Resultat::class)->findAll();

        $data = [];
        foreach ($resultats as $resultat) {
            $data[] = [
                'id' => $resultat->getId(),
                'candidat' => $resultat->getCandidat()->getNom(),
                'total_votes' => $resultat->getTotalVotes(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/resultats/calculate', name: 'resultats_calculate')]
    public function calculate(): Response
    {
        $votes = $this->voteRepository->findAll();

        $votesParCandidat = [];
        foreach ($votes as $vote) {
            $candidatId = $vote->getCandidat()->getId();
            if (!isset($votesParCandidat[$candidatId])) {
                $votesParCandidat[$candidatId] = 0;
            }
            $votesParCandidat[$candidatId]++;
        }

        foreach ($votesParCandidat as $candidatId => $totalVotes) {
            $candidat = $this->candidatRepository->find($candidatId);
            $resultat = $this->entityManager->getRepository(Resultat::class)->findOneBy(['candidat' => $candidat]);
            if (!$resultat) {
                $resultat = new Resultat();
                $resultat->setCandidat($candidat);
            }
            $resultat->setTotalVotes($totalVotes);
            $this->entityManager->persist($resultat);
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('resultats');
    }

    #[Route('/api/resultats/last', name: 'resultats_last')]
    public function getLastElectionResultats(): JsonResponse
    {
        // Assuming you have a way to identify the last election, e.g., by a timestamp or id.
        $lastElectionResultats = $this->resultatRepository->findLastElectionResultats();

        $data = [];
        foreach ($lastElectionResultats as $resultat) {
            $data[] = [
                'id' => $resultat->getId(),
                'candidat' => $resultat->getCandidat()->getNom(),
                'total_votes' => $resultat->getTotalVotes(),
            ];
        }

        return new JsonResponse($data);
    }
}

