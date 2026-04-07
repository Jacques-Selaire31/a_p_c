<?php

namespace App\Controller;

use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
final class CommentController extends AbstractController
{
    #[Route('/comment', name: 'comment_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, CommentService $commentService): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $comment = $commentService->createComment($data);

            return $this->json([
                'message' => 'Commentaire posté',
                'id' => $comment->getId(),
            ], 201); // Created

        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], 400); // Bad Request
        }
    }

    #[Route('/public/article/{id}/comments', name: 'comment_list', methods: ['GET'])]
    public function list(int $id, CommentService $commentService): JsonResponse
    {
        try {
            $data = $commentService->getCommentsByArticle($id);
            return $this->json($data);

        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], 404); //NotFound
        }
    }
}