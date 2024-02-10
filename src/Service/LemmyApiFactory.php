<?php

namespace App\Service;

use DateInterval;
use JetBrains\PhpStorm\ExpectedValues;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Rikudou\LemmyApi\DefaultLemmyApi;
use Rikudou\LemmyApi\Enum\AuthMode;
use Rikudou\LemmyApi\Enum\LemmyApiVersion;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class LemmyApiFactory
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        #[Autowire('%app.lemmy.instance%')]
        private string $instance,
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        #[Autowire('%app.lemmy.user%')]
        private string $username,
        #[Autowire('%app.lemmy.password%')]
        private string $password,
    ) {
    }

    public function createApi(
        #[ExpectedValues(valuesFromClass: AuthMode::class)]
        int $authMode = AuthMode::Both,
    ): LemmyApi {
        $api = new DefaultLemmyApi(
            instanceUrl: $this->instance,
            version: LemmyApiVersion::Version3,
            httpClient: $this->httpClient,
            requestFactory: $this->requestFactory,
            authMode: $authMode,
        );
        $item = $this->cache->getItem('app.jwt');
        if ($item->isHit()) {
            $api->setJwt($item->get());
        } else {
            $response = $api->login($this->username, $this->password);
            $item->set($response->jwt);
            $item->expiresAfter(new DateInterval('P1D'));
            $this->cache->save($item);
        }

        return $api;
    }
}
