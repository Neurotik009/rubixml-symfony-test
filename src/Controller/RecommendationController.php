<?php

namespace App\Controller;

use App\Service\RecommendationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class RecommendationController extends AbstractController
{
    #[Route('/recommendations/{userId}', name: 'get_recommendations')]
    public function getRecommendations(int $userId, RecommendationService $recommendationService): JsonResponse
    {
        $recommendations = $recommendationService->getRecommendations($userId);
        return new JsonResponse($recommendations);
    }
}
