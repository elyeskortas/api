<?php

namespace App\Controller;

use App\Entity\Electeur;
use App\Entity\ListeElectorale;
use App\Entity\Candidat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ElecteurRepository;
use App\Repository\ListeElectoraleRepository;
use App\Repository\CandidatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use DateTime;

class ListeElectoraleController extends AbstractController
{
    private $entityManager;
    private $electeurRepository;
    private $listeElectoraleRepository;
    private $candidatRepository;

    public function __construct(EntityManagerInterface $entityManager, ElecteurRepository $electeurRepository, ListeElectoraleRepository $listeElectoraleRepository, CandidatRepository $candidatRepository)
    {
        $this->entityManager = $entityManager;
        $this->electeurRepository = $electeurRepository;
        $this->listeElectoraleRepository = $listeElectoraleRepository;
        $this->candidatRepository = $candidatRepository;
    }

    #[Route('/api/liste-electorale/{id}/add-electeur', name: 'liste_electorale_add_electeur', methods: ['POST'])]
    public function addElecteur(Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);
    
        // Récupérer la liste électorale
        $listeElectorale = $this->listeElectoraleRepository->find($id);
    
        // Créer un nouvel électeur
        $electeur = new Electeur();
        $electeur->setNom($data['nom']);
        $electeur->setPrenom($data['prenom']);
        // Convertir la date de naissance en objet DateTime
        $dateNaissance = DateTime::createFromFormat('Y-m-d', $data['date_naissance']);
        $electeur->setDateDeNaissance($dateNaissance);
        $electeur->setEmail($data['email']);
        $electeur->setListeElectorale($listeElectorale);
    
        // Persister l'électeur
        $this->entityManager->persist($electeur);
        $this->entityManager->flush();
    
        return $this->json(['message' => 'Electeur ajouté à la liste électorale'], Response::HTTP_CREATED);
    }

    #[Route('/api/liste-electorale/{id}/update-electeur/{electeurId}', name: 'liste_electorale_update_electeur', methods: ['PUT'])]
    public function updateElecteur(Request $request, int $id, int $electeurId): Response
    {
        $data = json_decode($request->getContent(), true);

        // Récupérer l'électeur à mettre à jour
        $electeur = $this->electeurRepository->find($electeurId);

        if (!$electeur) {
            return $this->json(['message' => 'Electeur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour les données de l'électeur
        $electeur->setNom($data['nom']);
        $electeur->setPrenom($data['prenom']);
        // Convertir la date de naissance en objet DateTime
        $dateNaissance = DateTime::createFromFormat('Y-m-d', $data['date_naissance']);
        $electeur->setDateDeNaissance($dateNaissance);
        $electeur->setEmail($data['email']);

        // Persister les modifications
        $this->entityManager->flush();

        return $this->json(['message' => 'Electeur mis à jour'], Response::HTTP_OK);
    }

    #[Route('/api/liste-electorale/{id}/delete-electeur/{electeurId}', name: 'liste_electorale_delete_electeur', methods: ['DELETE'])]
    public function deleteElecteur(int $id, int $electeurId): Response
    {
        // Récupérer l'électeur à supprimer
        $electeur = $this->electeurRepository->find($electeurId);

        if (!$electeur) {
            return $this->json(['message' => 'Electeur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer l'électeur
        $this->entityManager->remove($electeur);
        $this->entityManager->flush();

        return $this->json(['message' => 'Electeur supprimé'], Response::HTTP_OK);
    }

    #[Route('/api/liste-electorale/{id}/electeurs', name: 'liste_electorale_electeurs', methods: ['GET'])]
    public function getElecteurs(int $id, SerializerInterface $serializer): Response
    {
        // Récupérer la liste électorale
        $listeElectorale = $this->listeElectoraleRepository->find($id);
    
        // Vérifier si la liste électorale existe
        if (!$listeElectorale) {
            // Retourner une réponse JSON avec un message d'erreur
            return $this->json(['message' => 'Liste électorale non trouvée'], Response::HTTP_NOT_FOUND);
        }
    
        // Si la liste électorale existe, récupérer ses électeurs
        $electeurs = $listeElectorale->getElecteurs();
    
        // Sérialiser les électeurs
        $jsonContent = $serializer->serialize($electeurs, 'json');
        
        // Retourner une réponse JSON avec les électeurs sérialisés
        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    #[Route('/api/liste-electorale/{id}/add-candidat', name: 'liste_electorale_add_candidat', methods: ['POST'])]
    public function addCandidat(Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);

        // Récupérer la liste électorale
        $listeElectorale = $this->listeElectoraleRepository->find($id);

        // Créer un nouveau candidat
        $candidat = new Candidat();
        $candidat->setNom($data['nom']);
        $candidat->setPrenom($data['prenom']);
        // Convertir la date de naissance en objet DateTime
        $dateNaissance = DateTime::createFromFormat('Y-m-d', $data['date_naissance']);
        $candidat->setDateNaissance($dateNaissance);
        $candidat->setEmail($data['email']);
        $candidat->setListeElectorale($listeElectorale);

        // Persister le candidat
        $this->entityManager->persist($candidat);
        $this->entityManager->flush();

        return $this->json(['message' => 'Candidat ajouté à la liste électorale'], Response::HTTP_CREATED);
    }

    #[Route('/api/liste-electorale/add-liste-electorale', name: 'liste_electorale_add_liste_electorale', methods: ['POST'])]
    public function addListeElectorale(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Vérifier si le champ 'nom' est présent dans les données de la requête
        if (!isset($data['nom'])) {
            return $this->json(['message' => 'Le champ nom est requis'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si le champ 'type' est présent dans les données de la requête
        if (!isset($data['type'])) {
            return $this->json(['message' => 'Le champ type est requis'], Response::HTTP_BAD_REQUEST);
        }

        // Créer une nouvelle liste électorale et définir son nom et son type
        $listeElectorale = new ListeElectorale();
        $listeElectorale->setNom($data['nom']);
        $listeElectorale->setType($data['type']);

        // Définir la date de création directement avec l'objet DateTime
        $dateCreation = new DateTime(); // Crée une nouvelle instance de DateTime avec la date et l'heure actuelles
        $listeElectorale->setDateCréation($dateCreation); // Utilisation directe de l'objet DateTime

        // Persister la liste électorale
        $this->entityManager->persist($listeElectorale);
        $this->entityManager->flush();

        return $this->json(['message' => 'Liste électorale ajoutée'], Response::HTTP_CREATED);
    }

    #[Route('/api/liste-electorale', name: 'liste_electorale_get_all', methods: ['GET'])]
    public function getAllElecteurs(SerializerInterface $serializer): Response
    {
        $electeurs = $this->electeurRepository->findAll();
        $jsonContent = $serializer->serialize($electeurs, 'json');
        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    #[Route('/api/candidats', name: 'candidats_get_all', methods: ['GET'])]
    public function getAllCandidats(SerializerInterface $serializer): Response
    {
        $candidats = $this->candidatRepository->findAll();
        $jsonContent = $serializer->serialize($candidats, 'json');
        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }
}
