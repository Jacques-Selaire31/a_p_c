<?php

namespace App\Service;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ArticleService
{
    public function __construct(
        private CurrentUserService $currentUserService,
        private CategoryRepository $categoryRepository,
        private ArticleRepository $articleRepository,
        private EntityManagerInterface $em,
        private ValidatorInterface $validator
    ) {}

public function createArticle(array $data): Article
{
    if (empty($data['title'])) {
        throw new \InvalidArgumentException('Le titre est obligatoire');
    }

    if (empty($data['content']['blocks'])) {
        throw new \InvalidArgumentException('Le contenu ne peut pas être vide');
    }

    $hasParagraph = false;
    foreach ($data['content']['blocks'] as $block) {
        if ($block['type'] === 'paragraph') {
            $hasParagraph = true;
            break;
        }
    }
    if (!$hasParagraph) {
        throw new \InvalidArgumentException("L'article doit posséder au moins un paragraphe");
    }

    if (empty($data['categories'])) {
        throw new \InvalidArgumentException("L'article doit posséder au moins une catégorie");
    }

    $pseudo = $this->currentUserService->getUser()->getPseudo();
    $slug = $this->generateSlug($data['title'], $pseudo);

    $article = new Article();
    $article->setTitle($data['title']);
    $article->setContent($data['content']);
    $article->setTextPreview($this->generateTextPreview($article));
    $article->setAuthor($this->currentUserService->getUser());
    $article->setSlug($slug);

    foreach ($data['categories'] as $id) {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            throw new \InvalidArgumentException("Catégorie introuvable");
        }
        $article->addCategory($category);
    }

    $errors = $this->validator->validate($article);
    if (count($errors) > 0) {
        throw new \InvalidArgumentException($errors[0]->getMessage());
    }

    $this->em->persist($article);
    $this->em->flush();

    return $article;
}

public function getPublishedArticles(): array
{
    $articles = $this->articleRepository->findPublishedArticles();

    $data = [];
    foreach ($articles as $article) {

        $categories = [];
        foreach ($article->getCategories() as $category) {
            $categories[] = [
                'id'   => $category->getId(),
                'name' => $category->getName(),
            ];
        }

        $data[] = [
            'id'          => $article->getId(),
            'slug'        => $article->getSlug(),
            'title'       => $article->getTitle(),
            'coverImage'  => $this->extractCoverImage($article),
            'textPreview' => $this->generateTextPreview($article),
            'createdAt'   => $article->getCreatedAt(),
            'author'      => [
                'id'     => $article->getAuthor()->getId(),
                'pseudo' => $article->getAuthor()->getPseudo(),
            ],
            'categories'  => $categories,
        ];
    }

    return $data;
}

// public function getArticleById(int $id): array
// {
//     $article = $this->articleRepository->find($id);
//     if (!$article) {
//         throw new \InvalidArgumentException('Article introuvable');
//     }

//     $categories = [];
//     foreach ($article->getCategories() as $category) {
//         $categories[] = [
//             'id'   => $category->getId(),
//             'name' => $category->getName(),
//         ];
//     }

//     return [
//         'id'         => $article->getId(),
//         'slug'       => $article->getSlug(),
//         'title'      => $article->getTitle(),
//         'content'    => $article->getContent(),
//         'coverImage' => $this->extractCoverImage($article),
//         'createdAt'  => $article->getCreatedAt(),
//         'author'     => [
//             'id'     => $article->getAuthor()->getId(),
//             'pseudo' => $article->getAuthor()->getPseudo(),
//         ],
//         'categories' => $categories,
//     ];
// }

public function togglePublish(int $id): Article
{
    $article = $this->articleRepository->find($id);

    if (!$article) {
        throw new \InvalidArgumentException('Article introuvable');
    }

    if ($article->getAuthor() !== $this->currentUserService->getUser()) {
        throw new \InvalidArgumentException('Action non autorisée');
    }

    $article->setIsPublished(!$article->isPublished());
    $this->em->flush();

    return $article;
}

private function generateTextPreview(Article $article): string
{
    foreach ($article->getContent()['blocks'] as $block) {
        if ($block['type'] === 'paragraph') {
            $text = strip_tags($block['data']['text']);
            return strlen($text) > 150 ? substr($text, 0, 150) . '...' : $text;
        }
    }
    return '';
}

    private function extractCoverImage(Article $article): ?string
    {
        if ($article->getCoverImage()) {
            return $article->getCoverImage();
        }

        foreach ($article->getContent()['blocks'] as $block) {
            if ($block['type'] === 'image' && !empty($block['data']['file']['url'])) {
                return $block['data']['file']['url'];
            }
        }

        return null;
    }

    private function generateSlug(string $title, string $pseudo): string
    {
        $slugger = new AsciiSlugger();
        $slugTitle = $slugger->slug($title)->lower()->toString();
        $slugPseudo = $slugger->slug($pseudo)->lower()->toString();
        return $slugPseudo . '-' . $slugTitle;
    }

    public function getArticleBySlug(string $slug): array
{
    $article = $this->articleRepository->findBySlug($slug);
    if (!$article) {
        throw new \InvalidArgumentException('Article introuvable');
    }

    $categories = [];
    foreach ($article->getCategories() as $category) {
        $categories[] = [
            'id'   => $category->getId(),
            'name' => $category->getName(),
        ];
    }

    return [
        'id'         => $article->getId(),
        'slug'       => $article->getSlug(),
        'title'      => $article->getTitle(),
        'content'    => $article->getContent(),
        'coverImage' => $this->extractCoverImage($article),
        'createdAt'  => $article->getCreatedAt(),
        'author'     => [
            'id'     => $article->getAuthor()->getId(),
            'pseudo' => $article->getAuthor()->getPseudo(),
        ],
        'categories' => $categories,
    ];
}
}
