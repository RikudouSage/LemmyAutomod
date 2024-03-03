<?php

namespace App\Service;

use App\Dto\Model\FediseerInstance;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class Fediseer
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%app.fediseer.api%')]
        private string $apiUrl,
        #[Autowire('%app.fediseer.key%')]
        private string $apiKey,
    ) {
    }

    public function censureInstance(string $domain, ?string $reason, ?string $evidence): void
    {
        if (!$this->apiKey) {
            return;
        }

        $method = $this->isCensured($domain) ? Request::METHOD_PATCH : Request::METHOD_PUT;

        $this->httpClient->request($method, "{$this->apiUrl}/v1/censures/{$domain}", [
            'json' => [
                'reason' => $reason,
                'evidence' => $evidence,
            ],
            'headers' => [
                'apiKey' => $this->apiKey,
            ],
        ]);
    }

    private function isCensured(string $domain): bool
    {
        $me = $this->getMyInstance();

        $data = json_decode($this->httpClient->request(Request::METHOD_GET, "{$this->apiUrl}/v1/censures_given/{$me->domain}?domains=true", [
            'headers' => [
                'apiKey' => $this->apiKey,
            ],
        ])->getContent());

        return in_array($domain, $data['domains'], true);
    }

    private function getMyInstance(): FediseerInstance
    {
        $data = json_decode($this->httpClient->request(Request::METHOD_GET, "{$this->apiUrl}/v1/find_instance", [
            'headers' => [
                'apiKey' => $this->apiKey,
            ],
        ])->getContent(), true, flags: JSON_THROW_ON_ERROR);

        return new FediseerInstance(
            id: $data['id'],
            domain: $data['domain'],
        );
    }
}
