<?php

namespace App\Service\InstanceDataEnricher;

use App\Dto\Model\BasicInstanceData;
use App\Dto\Model\EnrichedInstanceData;
use DateInterval;
use Override;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsTaggedItem(priority: -1_000)]
final readonly class DefaultDataEnricher implements InstanceDataEnricher
{
    #[Override]
    public function supports(string $software, string $version): bool
    {
        return true;
    }

    #[Override]
    public function getEnriched(BasicInstanceData $instanceData): EnrichedInstanceData
    {
        return new EnrichedInstanceData(
            instance: $instanceData->instance,
            software: $instanceData->software,
            version: $instanceData->version,
            openRegistrations: $instanceData->openRegistrations,
            captcha: null,
            emailVerification: null,
            applications: null,
        );
    }
}
