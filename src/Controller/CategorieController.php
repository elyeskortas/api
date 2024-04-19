<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieController extends AbstractController
{
    private $entityManager;
    private $categorieRepository;

    public function __construct(EntityManagerInterface $entityManager, CategorieRepository $categorieRepository)
    {
        $this->entityManager = $entityManager;
        $this->categorieRepository = $categorieRepository;
    }

    #[Route('/categories', name: 'categorie_add', methods: ['POST'])]
    public function addCategorie(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Créer une nouvelle instance de Categorie
        $categorie = new Categorie();
        $categorie->setNom($data['nom']);
        $categorie->setIsActive(true);

        // Persister la catégorie
        $this->entityManager->persist($categorie);
        $this->entityManager->flush();

        return $this->json(['message' => 'Catégorie ajoutée avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/categories/{id}', name: 'categorie_update', methods: ['PUT'])]
    public function updateCategorie(Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);

        // Récupérer la catégorie à mettre à jour
        $categorie = $this->categorieRepository->find($id);

        if (!$categorie) {
            return $this->json(['message' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour les propriétés de la catégorie
        $categorie->setNom($data['nom']);

        // Persister les modifications
        $this->entityManager->flush();

        return $this->json(['message' => 'Catégorie mise à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/categories/{id}/toggle-status', name: 'categorie_toggle_status', methods: ['PUT'])]
    public function toggleCategorieStatus(int $id): Response
    {
        // Récupérer la catégorie à activer/inactiver
        $categorie = $this->categorieRepository->find($id);

        if (!$categorie) {
            return $this->json(['message' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Inverser le statut de la catégorie
        $categorie->setIsActive(!$categorie->getIsActive());

        // Persister les modifications
        $this->entityManager->flush();

        // Retourner un message approprié selon le statut
        $statusMessage = $categorie->getIsActive() ? 'activée' : 'désactivée';
        return $this->json(['message' => "Catégorie $statusMessage avec succès"], Response::HTTP_OK);
    }

    #[Route('/categories', name: 'categorie_list', methods: ['GET'])]
    public function getCategories(): Response
    {
        // Récupérer la liste des catégories
        $categories = $this->categorieRepository->findAll();

        // Retourner les catégories sous forme de JSON
        return $this->json($categories, Response::HTTP_OK);
    }
}
