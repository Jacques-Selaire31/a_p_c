<?php

namespace App\Service;

use App\Entity\Comment;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommentService
{
    public function __construct(
        private CurrentUserService $currentUserService,
        private ArticleRepository $articleRepository,
        private CommentRepository $commentRepository,
        private EntityManagerInterface $em,
        private ValidatorInterface $validator
    ) {}

    public function createComment(array $data): Comment
    {
        $article = $this->articleRepository->find($data['articleId']);
        if (!$article) {
            throw new \InvalidArgumentException('Article introuvable');
        }
        if (!$article->isPublished()) {
            throw new \InvalidArgumentException('Impossible de commenter cet article');
        }

        $comment = new Comment();
        $comment->setContent(trim(strip_tags($data['content'])));
        $comment->setAuthor($this->currentUserService->getUser());
        $comment->setArticle($article);

        if (!empty($data['parentId'])) {
            $parent = $this->commentRepository->find($data['parentId']);
            if (!$parent) {
                throw new \InvalidArgumentException('Commentaire parent introuvable');
            }
            $comment->setParent($parent);
        }

        $errors = $this->validator->validate($comment);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException($errors[0]->getMessage());
        }

        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }

    public function getCommentsByArticle(int $articleId): array
    {
        $article = $this->articleRepository->find($articleId);
        if (!$article) {
            throw new \InvalidArgumentException('Article introuvable');
        }

        $data = [];
        foreach ($this->commentRepository->findParentCommentsByArticle($article) as $comment) {
            $data[] = $this->serializeComment($comment);
        }

        return $data;
    }


    private function serializeComment(Comment $comment): array
    {
        $parent = null;
        if ($comment->getParent()) {
            $p = $comment->getParent();
            $parent = [
                'id'        => $p->getId(),
                'content'   => $p->getContent(),
                'createdAt' => $p->getCreatedAt(),
                'author'    => $p->getAuthor()->getPseudo(),
            ];
        }

        $replies = [];
        foreach ($comment->getReplies() as $reply) {
            $replies[] = $this->serializeComment($reply);
        }

        return [
            'id'        => $comment->getId(),
            'content'   => $comment->getContent(),
            'createdAt' => $comment->getCreatedAt(),
            'author'    => $comment->getAuthor()->getPseudo(),
            'parent'    => $parent,
            'replies'   => $replies,
        ];
    }
}
