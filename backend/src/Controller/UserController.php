<?php

namespace App\Controller;

use App\Service\RegisterService;
use App\Service\CurrentUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user')]
final class UserController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        RegisterService $registerService
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            $registerService->createUser($data);

            return new JsonResponse(
                ['message' => 'Utilisateur créé'],
                201
            );
        } catch (\InvalidArgumentException $error) {
            return new JsonResponse(
                ['message' => $error->getMessage()],
                400
            );
        }
    }

    #[Route('/userinfo', name: 'userinfo', methods: ['GET'])]
    public function getCurrentUserInfo(CurrentUserService $currentUserService) :JsonResponse {
$user = $currentUserService->getUser();
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'pseudo' => $user->getPseudo(),
            'roles' => $user->getRoles(),
        ]);
    }
}