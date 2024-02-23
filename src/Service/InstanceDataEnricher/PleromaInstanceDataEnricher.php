<?php

namespace App\Service\InstanceDataEnricher;

use App\Dto\Model\BasicInstanceData;
use App\Dto\Model\EnrichedInstanceData;
use LogicException;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PleromaInstanceDataEnricher implements InstanceDataEnricher
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    #[Override]
    public function supports(string $software, string $version): bool
    {
        return $software === 'pleroma';
    }

    #[Override]
    public function getEnriched(BasicInstanceData $instanceData): EnrichedInstanceData
    {
        $url = "https://{$instanceData->instance}/api/v1/instance";
        $response = $this->httpClient->request(Request::METHOD_GET, $url);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new LogicException('Failed getting instance data');
        }
        $json = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

        return new EnrichedInstanceData(
            instance: $instanceData->instance,
            software: $instanceData->software,
            version: $instanceData->version,
            openRegistrations: $instanceData->openRegistrations,
            captcha: null,
            emailVerification: $json['pleroma']['metadata']['account_activation_required'] ?? null,
            applications: $json['approval_required'] ?? null,
        );
    }
}
