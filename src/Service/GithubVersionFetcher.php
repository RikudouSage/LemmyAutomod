<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class GithubVersionFetcher
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    public function getVersion(string $url, string $default = 'dev'): string
    {
        $releaseUrl = "{$url}/releases/latest";
        try {
            $response = $this->httpClient->request(
                Request::METHOD_GET,
                $releaseUrl,
                [
                    'max_redirects' => 0,
                ],
            );
            $headers = $response->getHeaders(false);
            $location = $headers['location'][0] ?? null;
            if ($location === null) {
                return $default;
            }

            return substr($location, strlen("{$url}/releases/tag/v"));
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            return $default;
        }
    }
}
