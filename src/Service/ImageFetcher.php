<?php

namespace App\Service;

use DateInterval;
use Psr\Cache\CacheItemPoolInterface;
use SapientPro\ImageComparator\ImageComparator;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final readonly class ImageFetcher
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        private HttpClientInterface $httpClient,
        private ImageComparator $imageComparator,
        private MimeTypeGuesserInterface $typeGuesser,
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
            $isImage = str_starts_with($contentType, 'image/');
            if (!$isImage) {
                $filepath = tempnam(sys_get_temp_dir(), 'lemmy_automod');
                file_put_contents($filepath, $response->getContent());
                $isImage = str_starts_with($this->typeGuesser->guessMimeType($filepath), 'image/');
            }
            if ($isImage) {
                try {
                    if ($this->isAnimatedWebP($response->getContent())) {
                        $image = false;
                    } else {
                        $image = imagecreatefromstring($response->getContent());
                    }
                } catch (Throwable) {
                    $image = false;
                }
                if ($image !== false) {
                    $cacheItem->set(
                        $this->imageComparator->convertHashToBinaryString(
                            $this->imageComparator->hashImage(
                                $image,
                            ),
                        ),
                    );
                }
            }
        }

        if (!$cacheItem->get()) {
            $cacheItem->set(null);
        }
        $cacheItem->expiresAfter(new DateInterval('P7D'));
        $this->cache->save($cacheItem);

        return $cacheItem->get();
    }

    private function isAnimatedWebP(string $fileData): bool
    {
        if (
            stripos($fileData, 'WEBPVP8X') !== false ||
            stripos($fileData, 'ANIM') !== false
        ) {
            return true;
        }
        return false;
    }
}
