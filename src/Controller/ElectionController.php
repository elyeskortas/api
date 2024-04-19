<?php

namespace App\Controller;

use App\Entity\Election;
use App\Repository\ElectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ElectionController extends AbstractController
{
    private $entityManager;
    private $electionRepository;

    public function __construct(EntityManagerInterface $entityManager, ElectionRepository $electionRepository)
    {
        $this->entityManager = $entityManager;
        $this->electionRepository = $electionRepository;
    }

    #[Route('/elections', name: 'election_add', methods: ['POST'])]
    public function addElection(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Créer une nouvelle instance d'élection
        $election = new Election();
        $election->setNom($data['nom']);
        $election->setDateDebut(new \DateTime($data['date_debut']));
        $election->setDateFin(new \DateTime($data['date_fin']));
        // Ajoutez d'autres propriétés selon votre entité Election

        // Persister l'élection
        $this->entityManager->persist($election);
        $this->entityManager->flush();

        return $this->json(['message' => 'Election ajoutée avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/elections/{id}', name: 'election_update', methods: ['PUT'])]
    public function updateElection(Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);

        // Récupérer l'élection à mettre à jour
        $election = $this->electionRepository->find($id);

        if (!$election) {
            return $this->json(['message' => 'Election non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour les propriétés de l'élection
        $election->setNom($data['nom']);
        $election->setDateDebut(new \DateTime($data['date_debut']));
        $election->setDateFin(new \DateTime($data['date_fin']));
        // Mettre à jour d'autres propriétés selon votre entité Election

        // Persister les modifications
        $this->entityManager->flush();

        return $this->json(['message' => 'Election mise à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/elections/{id}/status', name: 'election_toggle_status', methods: ['PUT'])]
    public function toggleElectionStatus(int $id): Response
    {
        // Récupérer l'élection à activer/inactiver
        $election = $this->electionRepository->find($id);

        if (!$election) {
            return $this->json(['message' => 'Election non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Inverser le statut de l'élection
        $election->setStatut(!$election->getStatut());

        // Persister les modifications
        $this->entityManager->flush();

        // Retourner un message approprié selon le statut
        $statusMessage = $election->getStatut() ? 'activée' : 'désactivée';
        return $this->json(['message' => "Election $statusMessage avec succès"], Response::HTTP_OK);
    }

    #[Route('/elections', name: 'election_list', methods: ['GET'])]
    public function getElections(): Response
    {
        // Récupérer la liste des élections
        $elections = $this->electionRepository->findAll();

        // Retourner les élections sous forme de JSON
        return $this->json($elections, Response::HTTP_OK);
    }
}
