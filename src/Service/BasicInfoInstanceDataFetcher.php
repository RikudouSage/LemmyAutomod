<?php

namespace App\Service;

use App\Dto\Model\BasicInstanceData;
use DateInterval;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class BasicInfoInstanceDataFetcher
{
    private const string SCHEMA = 'http://nodeinfo.diaspora.software/ns/schema/2.0';

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheItemPoolInterface $cache,
    ) {
    }

    public function fetch(string $instance): ?BasicInstanceData
    {
        $cacheItem = $this->cache->getItem("enriched_instance.basic_data.{$instance}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }
        $url = "https://{$instance}/.well-known/nodeinfo";
        $response = $this->httpClient->request(Request::METHOD_GET, $url);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return null;
        }
        $body = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        foreach ($body['links'] ?? [] as $link) {
            if (($link['rel'] ?? null) !== self::SCHEMA) {
                continue;
            }
            $href = $link['href'] ?? null;
            if (!$href) {
                continue;
            }

            $response = $this->httpClient->request(Request::METHOD_GET, $href);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                continue;
            }
            $body = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            $result = new BasicInstanceData(
                instance: $instance,
                software: $body['software']['name'] ?? null,
                version: $body['software']['version'] ?? null,
                openRegistrations: $body['openRegistrations'] ?? null,
            );
            $cacheItem->set($result);
            $cacheItem->expiresAfter(new DateInterval('PT5M'));
            $this->cache->save($cacheItem);

            return $result;
        }

        return null;
    }
}
