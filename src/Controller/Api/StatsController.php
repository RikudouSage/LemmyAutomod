<?php

namespace App\Controller\Api;

use App\Service\StatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('%rikudou_api.api_prefix%/internal/stats')]
final class StatsController extends AbstractController
{
    #[Route('/stats', name: 'api.stats.stats', methods: [Request::METHOD_GET])]
    public function getStats(StatsService $statsService): JsonResponse
    {
        return new JsonResponse($statsService->getStats());
    }
}
