<?php

namespace App\Service;

use DateInterval;
use Psr\Cache\CacheItemPoolInterface;
use SapientPro\ImageComparator\ImageComparator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ImageFetcher
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        private HttpClientInterface $httpClient,
        private ImageComparator $imageComparator,
    ) {
    }

    public function getImageHash(string $url): ?string
    {
        $cacheKey = "image.hash." . str_replace(str_split(ItemInterface::RESERVED_CHARACTERS), '_', $url);
        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $response = $this->httpClient->request(Request::METHOD_GET, $url);
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $cacheItem->set(null);
        } else {
            $contentType = $response->getHeaders(false)['content-type'][0] ?? '';
            if (!str_starts_with($contentType, 'image/')) {
                $cacheItem->set(null);
            } else {
                $cacheItem->set(
                    $this->imageComparator->convertHashToBinaryString(
                        $this->imageComparator->hashImage(
                            imagecreatefromstring($response->getContent()),
                        ),
                    ),
                );
            }
        }

        $cacheItem->expiresAfter(new DateInterval('P7D'));
        $this->cache->save($cacheItem);

        return $cacheItem->get();
    }
}
