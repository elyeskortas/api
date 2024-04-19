<?php

namespace App\Controller;

use App\Entity\Resultat;
use App\Repository\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResultatController extends AbstractController
{
    private $entityManager;
    private $voteRepository;

    public function __construct(EntityManagerInterface $entityManager, VoteRepository $voteRepository)
    {
        $this->entityManager = $entityManager;
        $this->voteRepository = $voteRepository;
    }

    #[Route('/resultats', name: 'resultats')]
    public function index(): Response
    {
        // Récupérer les résultats depuis la base de données
        $resultats = $this->getDoctrine()->getRepository(Resultat::class)->findAll();

        return $this->render('resultat/index.html.twig', [
            'resultats' => $resultats,
        ]);
    }

    #[Route('/resultats/calculate', name: 'resultats_calculate')]
    public function calculate(): Response
    {
        // Récupérer tous les votes depuis la base de données
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
            $resultat = $this->getDoctrine()->getRepository(Resultat::class)->findOneBy(['candidat' => $candidatId]);
            if (!$resultat) {
                $resultat = new Resultat();
                $resultat->setCandidat($this->getDoctrine()->getRepository(Candidat::class)->find($candidatId));
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
