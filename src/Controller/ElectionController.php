<?php

namespace App\Controller;

use App\Entity\Election;
use App\Repository\ElectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse; 
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

    #[Route('/api/elections', name: 'election_all', methods: ['GET'])]
    public function getAllElections(): Response
    {
        // Récupérer la liste de toutes les élections
        $elections = $this->electionRepository->findAll();

        // Retourner les élections sous forme de JSON
        return $this->json($elections, Response::HTTP_OK);
    }

    #[Route('/api/elections/add', name: 'election_add', methods: ['POST'])]
    public function addElection(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
    
        // Create a new instance of Election
        $election = new Election();
        $election->setNom($data['nom']);
    
        // Ensure that date_debut and date_fin are present in the request data
        if (isset($data['date_debut']) && isset($data['date_fin'])) {
            // Convert the date strings to DateTime objects
            $dateDebut = new \DateTime($data['date_debut']);
            $dateFin = new \DateTime($data['date_fin']);
    
            // Set the date properties with string values
            $election->setDateDebut($dateDebut->format('Y-m-d H:i:s'));
            $election->setDateFin($dateFin->format('Y-m-d H:i:s'));
        } else {
            // Handle the case where date_debut or date_fin is missing
            return $this->json(['message' => 'Date de début ou date de fin manquante'], Response::HTTP_BAD_REQUEST);
        }
    
        $election->setStatut(true);
        // Add other properties according to your Election entity
    
        // Persist the election
        $this->entityManager->persist($election);
        $this->entityManager->flush();
    
        return $this->json(['message' => 'Election ajoutée avec succès'], Response::HTTP_CREATED);
    }
    


    #[Route('/api/elections/{id}', name: 'election_update', methods: ['PUT'])]
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
        // Convertir la date de début en chaîne de caractères
        $dateDebut = $data['date_debut'];
        // Convertir la date de fin en chaîne de caractères
        $dateFin = $data['date_fin'];
        // Mettre à jour la date de début avec la chaîne de caractères
        $election->setDateDebut($dateDebut);
        // Mettre à jour la date de fin avec la chaîne de caractères
        $election->setDateFin($dateFin);
        // Mettre à jour d'autres propriétés selon votre entité Election
    
        // Persister les modifications
        $this->entityManager->flush();
    
        return $this->json(['message' => 'Election mise à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/elections/{id}/status', name: 'election_toggle_status', methods: ['PUT'])]
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

    #[Route('/api/elections/list', name: 'election_list', methods: ['GET'])]
    public function getElections(): Response
    {
        // Récupérer la liste des élections
        $elections = $this->electionRepository->findAll();

        // Retourner les élections sous forme de JSON
        return $this->json($elections, Response::HTTP_OK);
    }
    #[Route('/api/elections/count', name: 'election_count', methods: ['GET'])]
public function getTotalElections(): Response
{
    $totalElections = $this->electionRepository->count([]);
    return $this->json(['count' => $totalElections], Response::HTTP_OK);
}

}
