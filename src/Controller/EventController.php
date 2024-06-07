<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Election;
use App\Entity\Restriction;

class EventController extends AbstractController
{
    private $entityManager;
    private $eventRepository;

    public function __construct(EntityManagerInterface $entityManager, EventRepository $eventRepository)
    {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
    }

    #[Route('/api/events', name: 'event_add', methods: ['POST'])]
    public function addEvent(Request $request, SluggerInterface $slugger): Response
    {
        $data = $request->request->all();
        $file = $request->files->get('photo');

        $electionId = $data['election_id'];
        $election = $this->entityManager->getRepository(Election::class)->find($electionId);

        if (!$election) {
            return $this->json(['message' => 'Élection non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $restrictionId = $data['restriction_id'];
        $restriction = $this->entityManager->getRepository(Restriction::class)->find($restrictionId);

        if (!$restriction) {
            return $this->json(['message' => 'Restriction non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $event = new Event();
        $event->setNom($data['name']);
        $event->setDescription($data['description']);
        $event->setStartDate(new \DateTime($data['start_date']));
        $event->setEndDate(new \DateTime($data['end_date']));
        $event->setIsActive(true);
        $event->setIsPlaying(false);
        $event->setIsPaused(false);
        $event->setElection($election);
        $event->setRestriction($restriction);

        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('photos_directory'),
                    $newFilename
                );
                $event->setPhoto($newFilename);
            } catch (FileException $e) {
                return $this->json(['message' => 'Failed to upload photo'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $this->json(['message' => 'Événement ajouté avec succès'], Response::HTTP_CREATED);
    }
    
    #[Route('/api/events/{id}', name: 'event_update', methods: ['PUT'])]
    public function updateEvent(Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);

        // Récupérer l'événement à mettre à jour
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json(['message' => 'Événement non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour les propriétés de l'événement
        $event->setNom($data['name']);
        $event->setDescription($data['description']);
        // Mettre à jour d'autres propriétés selon votre entité Event

        // Persister les modifications
        $this->entityManager->flush();

        return $this->json(['message' => 'Événement mis à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/events/{id}/toggle-status', name: 'event_toggle_status', methods: ['PUT'])]
    public function toggleEventStatus(int $id): Response
    {
        // Récupérer l'événement à activer/inactiver
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json(['message' => 'Événement non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Inverser le statut de l'événement
        $event->setIsActive(!$event->isIsActive());

        // Persister les modifications
        $this->entityManager->flush();

        // Retourner un message approprié selon le statut
        $statusMessage = $event->isIsActive() ? 'activé' : 'désactivé';
        return $this->json(['message' => "Événement $statusMessage avec succès"], Response::HTTP_OK);
    }

    #[Route('/api/events/list', name: 'event_list', methods: ['GET'])]
    public function getEvents(): Response
    {
        // Récupérer la liste des événements
        $events = $this->eventRepository->findAll();

        // Retourner les événements sous forme de JSON
        return $this->json($events, Response::HTTP_OK);
    }

    #[Route('/api/events/list', name: 'events_list', methods: ['GET'])]
    public function getAllEvents(): JsonResponse
    {
        $events = $this->eventRepository->findAll();

        $data = [];
        foreach ($events as $event) {
            $data[] = [
                'id' => $event->getId(),
                'name' => $event->getName(),
                'startDate' => $event->getStartDate(),
                'endDate' => $event->getEndDate(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/events/{id}/play', name: 'event_play', methods: ['PUT'])]
    public function playEvent(int $id): Response
    {
        // Récupérer l'événement à démarrer
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json(['message' => 'Événement non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Implémentez la logique pour démarrer l'événement
        $event->setIsPlaying(true);
        $this->entityManager->flush();

        return $this->json(['message' => 'Événement démarré avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/events/{id}/pause', name: 'event_pause', methods: ['PUT'])]
    public function pauseEvent(int $id): Response
    {
        // Récupérer l'événement à mettre en pause
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json(['message' => 'Événement non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Implémentez la logique pour mettre en pause l'événement
        $event->setIsPaused(true);
        $this->entityManager->flush();

        return $this->json(['message' => 'Événement mis en pause avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/events/{id}/stop', name: 'event_stop', methods: ['PUT'])]
    public function stopEvent(int $id): Response
    {
        // Récupérer l'événement à arrêter
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json(['message' => 'Événement non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Implémentez la logique pour arrêter l'événement
        $event->setIsPlaying(false);
        $event->setIsPaused(false);
        $this->entityManager->flush();

        return $this->json(['message' => 'Événement arrêté avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/events/categorized', name: 'event_categorized', methods: ['GET'])]
    public function getCategorizedEvents(): Response
    {
        // Récupérer la liste des événements
        $events = $this->eventRepository->findAll();

        $ongoingEvents = [];
        $upcomingEvents = [];
        $pastEvents = [];

        $now = new \DateTime();

        foreach ($events as $event) {
            if ($event->getStartDate() <= $now && $event->getEndDate() >= $now) {
                $ongoingEvents[] = $event;
            } elseif ($event->getStartDate() > $now) {
                $upcomingEvents[] = $event;
            } else {
                $pastEvents[] = $event;
            }
        }

        // Retourner les événements catégorisés sous forme de JSON
        return $this->json([
            'ongoing_events' => $ongoingEvents,
            'upcoming_events' => $upcomingEvents,
            'past_events' => $pastEvents
        ], Response::HTTP_OK);
    }
}
