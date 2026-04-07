<?php
 
namespace App\Controller;
 
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
 
#[Route('/api/public')]
final class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'categories_list', methods: ['GET'])]
    public function list(CategoryRepository $repo): JsonResponse
    {
        $categories = $repo->findBy([], ['id' => 'ASC']);
 
        $data = [];
        foreach ($categories as $c) {
            $data[] = [
                'id' => $c->getId(),
                'name' => $c->getName(),
            ];
        }
 
        return $this->json($data);
    }
}