<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Repository\CandidatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VoteController extends AbstractController
{
    private $entityManager;
    private $candidatRepository;

    public function __construct(EntityManagerInterface $entityManager, CandidatRepository $candidatRepository)
    {
        $this->entityManager = $entityManager;
        $this->candidatRepository = $candidatRepository;
    }

    #[Route('/vote', name: 'vote', methods: ['POST'])]
    public function vote(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $userId = $data['user_id']; // Supposons que l'identifiant de l'utilisateur soit fourni dans la requête
        $candidatId = $data['candidat_id']; // L'identifiant du candidat choisi par l'utilisateur

        // Vérifier si l'utilisateur a déjà voté
        $existingVote = $this->entityManager->getRepository(Vote::class)->findOneBy(['userId' => $userId]);

        if ($existingVote) {
            return $this->json(['message' => 'Vous avez déjà voté'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si le candidat existe
        $candidat = $this->candidatRepository->find($candidatId);

        if (!$candidat) {
            return $this->json(['message' => 'Candidat non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Créer un nouveau vote
        $vote = new Vote();
        $vote->setUserId($userId);
        $vote->setCandidat($candidat);

        // Persister le vote
        $this->entityManager->persist($vote);
        $this->entityManager->flush();

        return $this->json(['message' => 'Vote enregistré avec succès'], Response::HTTP_CREATED);
    }
}
