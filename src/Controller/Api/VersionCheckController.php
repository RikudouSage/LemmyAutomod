<?php

namespace App\Controller\Api;

use App\Dto\Request\VersionCheckRequest;
use App\Service\GithubVersionFetcher;
use App\Service\VersionComparer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('%rikudou_api.api_prefix%/internal/version-check')]
final class VersionCheckController extends AbstractController
{
    private const string UI_REPOSITORY = 'https://github.com/RikudouSage/LemmyAutomodManager';
    private const string API_REPOSITORY = 'https://github.com/RikudouSage/LemmyAutomod';

    #[Route('/check', name: 'api.version_check.check', methods: [Request::METHOD_POST])]
    public function versionCheck(
        #[MapRequestPayload] VersionCheckRequest $request,
        GithubVersionFetcher $versionFetcher,
        VersionComparer $versionComparer,
        #[Autowire('%app.version%')]
        string $currentApiVersion,
    ): JsonResponse
    {
        $currentUiVersion = $request->uiVersion;
        $latestUiVersion = $versionFetcher->getVersion(self::UI_REPOSITORY);

        $latestApiVersion = $versionFetcher->getVersion(self::API_REPOSITORY);

        return new JsonResponse([
            'currentUiVersion' => $currentUiVersion,
            'latestUiVersion' => $latestUiVersion,
            'currentApiVersion' => $currentApiVersion,
            'latestApiVersion' => $latestApiVersion,
            'newUiVersionAvailable' => $versionComparer->compare($currentUiVersion, $latestUiVersion) === -1,
            'newApiVersionAvailable' => $versionComparer->compare($currentApiVersion, $latestApiVersion) === -1,
        ]);
    }

    #[Route('/api-version', name: 'api.version_check.api_version', methods: [Request::METHOD_GET])]
    public function apiVersion(
        #[Autowire('%app.version%')]
        string $currentApiVersion,
    ): JsonResponse {
        return new JsonResponse([
            'version' => $currentApiVersion,
        ]);
    }
}
