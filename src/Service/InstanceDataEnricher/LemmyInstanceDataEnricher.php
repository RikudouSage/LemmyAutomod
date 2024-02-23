<?php

namespace App\Service\InstanceDataEnricher;

use App\Dto\Model\BasicInstanceData;
use App\Dto\Model\EnrichedInstanceData;
use DateInterval;
use Override;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Rikudou\LemmyApi\DefaultLemmyApi;
use Rikudou\LemmyApi\Enum\AuthMode;
use Rikudou\LemmyApi\Enum\LemmyApiVersion;
use Rikudou\LemmyApi\Enum\RegistrationMode;

final readonly class LemmyInstanceDataEnricher implements InstanceDataEnricher
{
    public function __construct(
        private DefaultDataEnricher $defaultDataEnricher,
        private CacheItemPoolInterface $cache,
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
    ) {
    }

    #[Override]
    public function supports(string $software, string $version): bool
    {
        return $software === 'lemmy';
    }

    #[Override]
    public function getEnriched(BasicInstanceData $instanceData): EnrichedInstanceData
    {
        $cacheItem = $this->cache->getItem("enriched_instance.data.{$instanceData->instance}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $api = new DefaultLemmyApi(
            instanceUrl: $instanceData->instance,
            version: LemmyApiVersion::Version3,
            httpClient: $this->httpClient,
            requestFactory: $this->requestFactory,
            authMode: $instanceData->version === null
                ? AuthMode::Both
                : (
                    version_compare($instanceData->version, '0.19.0') === -1
                        ? AuthMode::Body
                        : AuthMode::Header
                )
        );

        $siteData = $api->site()->getSite();

        $result = new EnrichedInstanceData(
            instance: $instanceData->instance,
            software: $instanceData->software,
            version: $siteData->version,
            openRegistrations: $instanceData->openRegistrations,
            captcha: $siteData->siteView->localSite->captchaEnabled,
            emailVerification: $siteData->siteView->localSite->requireEmailVerification,
            applications: $siteData->siteView->localSite->registrationMode === RegistrationMode::RequireApplication,
        );
        $cacheItem->set($result);
        $cacheItem->expiresAfter(new DateInterval('PT5M'));
        $this->cache->save($cacheItem);

        return $result;
    }
}
