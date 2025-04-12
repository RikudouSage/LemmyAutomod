<?php

namespace App\Controller\Api;

use App\Dto\Request\CalculateHashRequest;
use App\Service\ImageFetcher;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('%rikudou_api.api_prefix%/internal/images')]
final class ImageController extends AbstractController
{
    #[Route('/calculate-hash', name: 'api.images.calculate_hash', methods: ['QUERY'])]
    public function calculateHash(
        #[MapRequestPayload] CalculateHashRequest $request,
        ImageFetcher $imageFetcher,
    ): JsonResponse {
        try {
            return new JsonResponse([
                'hash' => $imageFetcher->getImageHash($request->imageUrl),
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
