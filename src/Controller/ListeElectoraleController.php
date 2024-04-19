<?php

namespace App\Controller;

use App\Entity\Electeur;
use App\Entity\Candidat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ElecteurRepository;
use App\Repository\ListeElectoraleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ListeElectoraleController extends AbstractController
{
    private $entityManager;
    private $electeurRepository;
    private $listeElectoraleRepository;

    public function __construct(EntityManagerInterface $entityManager, ElecteurRepository $electeurRepository, ListeElectoraleRepository $listeElectoraleRepository)
    {
        $this->entityManager = $entityManager;
        $this->electeurRepository = $electeurRepository;
        $this->listeElectoraleRepository = $listeElectoraleRepository;
    }

    #[Route('/liste-electorale/{id}/add-electeur', name: 'liste_electorale_add_electeur', methods: ['POST'])]
    public function addElecteur(Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);

        // Récupérer la liste électorale
        $listeElectorale = $this->listeElectoraleRepository->find($id);

        // Créer un nouvel électeur
        $electeur = new Electeur();
        $electeur->setNom($data['nom']);
        $electeur->setPrenom($data['prenom']);
        $electeur->setDateNaissance($data['date_naissance']);
        $electeur->setEmail($data['email']);
        $electeur->setListeElectorale($listeElectorale);

        // Persister l'électeur
        $this->entityManager->persist($electeur);
        $this->entityManager->flush();

        return $this->json(['message' => 'Electeur ajouté à la liste électorale'], Response::HTTP_CREATED);
    }

    #[Route('/liste-electorale/{id}/update-electeur/{electeurId}', name: 'liste_electorale_update_electeur', methods: ['PUT'])]
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
        $electeur->setDateNaissance($data['date_naissance']);
        $electeur->setEmail($data['email']);

        // Persister les modifications
        $this->entityManager->flush();

        return $this->json(['message' => 'Electeur mis à jour'], Response::HTTP_OK);
    }

    #[Route('/liste-electorale/{id}/delete-electeur/{electeurId}', name: 'liste_electorale_delete_electeur', methods: ['DELETE'])]
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

    #[Route('/liste-electorale/{id}/electeurs', name: 'liste_electorale_electeurs', methods: ['GET'])]
    public function getElecteurs(int $id): Response
    {
        // Récupérer la liste électorale et ses électeurs
        $listeElectorale = $this->listeElectoraleRepository->find($id);
        $electeurs = $listeElectorale->getElecteurs();

        // Retourner les électeurs sous forme de JSON
        return $this->json($electeurs, Response::HTTP_OK);
    }

    #[Route('/liste-electorale/{id}/add-candidat', name: 'liste_electorale_add_candidat', methods: ['POST'])]
    public function addCandidat(Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);

        // Récupérer la liste électorale
        $listeElectorale = $this->listeElectoraleRepository->find($id);

        // Créer un nouveau candidat
        $candidat = new Candidat();
        $candidat->setNom($data['nom']);
        $candidat->setPrenom($data['prenom']);
        $candidat->setDateNaissance($data['date_naissance']);
        $candidat->setEmail($data['email']);
        $candidat->setListeElectorale($listeElectorale);

        // Persister le candidat
        $this->entityManager->persist($candidat);
        $this->entityManager->flush();

        return $this->json(['message' => 'Candidat ajouté à la liste électorale'], Response::HTTP_CREATED);
    }
}
