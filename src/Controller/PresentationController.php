<?php

namespace App\Controller;

use App\Entity\Presentation;
use App\Repository\PresentationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PresentationController extends AbstractController
{
    private $entityManager;
    private $presentationRepository;

    public function __construct(EntityManagerInterface $entityManager, PresentationRepository $presentationRepository)
    {
        $this->entityManager = $entityManager;
        $this->presentationRepository = $presentationRepository;
    }

    #[Route('/api/presentations', name: 'presentation_add', methods: ['POST'])]
    public function addPresentation(Request $request): Response
    {
        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        // Créer une nouvelle instance de Presentation
        $presentation = new Presentation();
        $presentation->setTitle($data['title']);
        $presentation->setDescription($data['description']);
        // Ajouter d'autres propriétés selon votre entité Presentation

        // Persister la présentation
        $this->entityManager->persist($presentation);
        $this->entityManager->flush();

        return $this->json(['message' => 'Présentation ajoutée avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/api/presentations/{id}', name: 'presentation_update', methods: ['PUT'])]
    public function updatePresentation(Request $request, int $id): Response
    {
        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        // Récupérer la présentation à mettre à jour
        $presentation = $this->presentationRepository->find($id);

        if (!$presentation) {
            return $this->json(['message' => 'Présentation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour les propriétés de la présentation
        $presentation->setTitle($data['title']);
        $presentation->setDescription($data['description']);
        // Mettre à jour d'autres propriétés selon votre entité Presentation

        // Persister les modifications
        $this->entityManager->flush();

        return $this->json(['message' => 'Présentation mise à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/presentations/{id}', name: 'presentation_delete', methods: ['DELETE'])]
    public function deletePresentation(int $id): Response
    {
        // Récupérer la présentation à supprimer
        $presentation = $this->presentationRepository->find($id);

        if (!$presentation) {
            return $this->json(['message' => 'Présentation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer la présentation
        $this->entityManager->remove($presentation);
        $this->entityManager->flush();

        return $this->json(['message' => 'Présentation supprimée avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/presentations/list', name: 'presentation_list', methods: ['GET'])]
    public function getPresentations(): Response
    {
        // Récupérer la liste des présentations
        $presentations = $this->presentationRepository->findAll();

        // Retourner les présentations sous forme de JSON
        return $this->json($presentations, Response::HTTP_OK);
    }
}
