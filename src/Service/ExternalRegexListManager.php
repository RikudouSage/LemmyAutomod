<?php

namespace App\Service;

use App\Entity\ExternalRegexList;
use App\Repository\ExternalRegexListRepository;
use DateInterval;
use JsonException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ExternalRegexListManager
{
    public function __construct(
        private ExternalRegexListRepository $repository,
        private CacheItemPoolInterface $cache,
        private HttpClientInterface $httpClient,
    ) {
    }

    public function findByName(string $name): ?ExternalRegexList
    {
        return $this->repository->findOneBy(['name' => $name]);
    }

    /**
     * @return array<string>
     *
     * @throws InvalidArgumentException
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getList(ExternalRegexList $regexList): array
    {
        $cacheItem = $this->cache->getItem("external_regex_list.{$regexList->getId()}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $response = $this->httpClient->request(
            method: Request::METHOD_GET,
            url: $regexList->getUrl(),
        );
        $body = $response->getContent();
        $items = explode($regexList->getDelimiter(), $body);

        if ($regexList->getAppend() || $regexList->getPrepend()) {
            $prepend = $regexList->getPrepend() ?? '';
            $append = $regexList->getAppend() ?? '';

            $items = array_map(
                static fn (string $item): string => $prepend . $item . $append,
                $items,
            );
        }

        $cacheItem->set($items);
        $cacheItem->expiresAfter(new DateInterval('PT1H'));
        $this->cache->save($cacheItem);

        return $items;
    }
}
