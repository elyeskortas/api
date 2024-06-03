<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    private $entityManager;
    private $articleRepository;

    public function __construct(EntityManagerInterface $entityManager, ArticleRepository $articleRepository)
    {
        $this->entityManager = $entityManager;
        $this->articleRepository = $articleRepository;
    }

    #[Route('/api/articles', name: 'article_add', methods: ['POST'])]
    public function addArticle(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Créer une nouvelle instance d'Article
        $article = new Article();
        $article->setTitle($data['title']);
        $article->setContent($data['content']);
        $article->setIsActive(true);

        // Persister l'article
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $this->json(['message' => 'Article ajouté avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/api/articles/{id}', name: 'article_update', methods: ['PUT'])]
    public function updateArticle(Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);

        // Récupérer l'article à mettre à jour
        $article = $this->articleRepository->find($id);

        if (!$article) {
            return $this->json(['message' => 'Article non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour les propriétés de l'article
        $article->setTitle($data['title']);
        $article->setContent($data['content']);

        // Persister les modifications
        $this->entityManager->flush();

        return $this->json(['message' => 'Article mis à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/articles/{id}/toggle-status', name: 'article_toggle_status', methods: ['PUT'])]
    public function toggleArticleStatus(int $id): Response
    {
        // Récupérer l'article à activer/inactiver
        $article = $this->articleRepository->find($id);

        if (!$article) {
            return $this->json(['message' => 'Article non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Inverser le statut de l'article
        $article->setIsActive(!$article->getIsActive());

        // Persister les modifications
        $this->entityManager->flush();

        // Retourner un message approprié selon le statut
        $statusMessage = $article->getIsActive() ? 'activé' : 'désactivé';
        return $this->json(['message' => "Article $statusMessage avec succès"], Response::HTTP_OK);
    }

    #[Route('/api/articles/list', name: 'article_list', methods: ['GET'])]
    public function getArticles(): Response
    {
        // Récupérer la liste des articles
        $articles = $this->articleRepository->findAll();

        // Retourner les articles sous forme de JSON
        return $this->json($articles, Response::HTTP_OK);
    }
}
