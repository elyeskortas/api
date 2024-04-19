<?php

namespace App\Controller;

use App\Entity\Restriction;
use App\Repository\RestrictionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RestrictionController extends AbstractController
{
    private $entityManager;
    private $restrictionRepository;

    public function __construct(EntityManagerInterface $entityManager, RestrictionRepository $restrictionRepository)
    {
        $this->entityManager = $entityManager;
        $this->restrictionRepository = $restrictionRepository;
    }

    #[Route('/restrictions', name: 'restriction_add', methods: ['POST'])]
    public function addRestriction(Request $request): Response
    {
        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        // Créer une nouvelle instance de Restriction
        $restriction = new Restriction();
        $restriction->setName($data['name']);
        // Ajouter d'autres propriétés selon votre entité Restriction

        // Persister la restriction
        $this->entityManager->persist($restriction);
        $this->entityManager->flush();

        return $this->json(['message' => 'Restriction ajoutée avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/restrictions/{id}', name: 'restriction_update', methods: ['PUT'])]
    public function updateRestriction(Request $request, int $id): Response
    {
        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        // Récupérer la restriction à mettre à jour
        $restriction = $this->restrictionRepository->find($id);

        if (!$restriction) {
            return $this->json(['message' => 'Restriction non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour les propriétés de la restriction
        $restriction->setName($data['name']);
        // Mettre à jour d'autres propriétés selon votre entité Restriction

        // Persister les modifications
        $this->entityManager->flush();

        return $this->json(['message' => 'Restriction mise à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/restrictions/{id}', name: 'restriction_delete', methods: ['DELETE'])]
    public function deleteRestriction(int $id): Response
    {
        // Récupérer la restriction à supprimer
        $restriction = $this->restrictionRepository->find($id);

        if (!$restriction) {
            return $this->json(['message' => 'Restriction non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer la restriction
        $this->entityManager->remove($restriction);
        $this->entityManager->flush();

        return $this->json(['message' => 'Restriction supprimée avec succès'], Response::HTTP_OK);
    }

    #[Route('/restrictions', name: 'restriction_list', methods: ['GET'])]
    public function getRestrictions(): Response
    {
        // Récupérer la liste des restrictions
        $restrictions = $this->restrictionRepository->findAll();

        // Retourner les restrictions sous forme de JSON
        return $this->json($restrictions, Response::HTTP_OK);
    }
}
