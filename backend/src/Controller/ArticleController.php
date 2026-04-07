<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class ArticleController extends AbstractController
{
    #[Route('/article', name: 'article_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, ArticleService $articleService): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $articleService->createArticle($data);

            return $this->json(['message' => 'Article créé'], 201); // Created

        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }

    #[Route('/public/articles', name: 'articles_list', methods: ['GET'])]
    public function list(ArticleService $articleService): JsonResponse
    {
        return $this->json($articleService->getPublishedArticles());
    }

    #[Route('/public/articles/{slug}', name: 'article_display', methods: ['GET'])]
    public function getArticle(string $slug, ArticleService $articleService): JsonResponse
    {
        try {
            return $this->json($articleService->getArticleBySlug($slug));
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], 404);
        }
    }

    #[Route('/article/{id}/publish', name: 'article_publish_toggle', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function togglePublish(int $id, ArticleService $articleService): JsonResponse
    {
        try {
            $article = $articleService->togglePublish($id);

            return $this->json([
                'message' => $article->isPublished() ? 'Article publié' : 'Article dépublié',
                'isPublished' => $article->isPublished(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], 404); // Not Found
        }
    }
}
