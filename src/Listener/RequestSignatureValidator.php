<?php

namespace App\Listener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onRequest')]
final readonly class RequestSignatureValidator
{
    private const string SYMMETRIC_PREFIX = 'whsec_';

    public function __construct(
        #[Autowire('%app.signature.key')]
        private string $signingKey,
    ) {
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$this->signingKey) {
            return;
        }

        $request = $event->getRequest();

        $signature = $request->headers->get('webhook-signature');
        $timestamp = $request->headers->get('webhook-timestamp');
        $id = $request->headers->get('webhook-id');

        if (!$signature || !$timestamp || !$id) {
            $event->setResponse(new JsonResponse([
                'error' => 'Signature headers are missing',
            ], Response::HTTP_FORBIDDEN));
            return;
        }

        $reconstructedSignature = $this->createSignature($id, $timestamp, $request->getContent());
        if ($reconstructedSignature !== $signature) {
            $event->setResponse(new JsonResponse([
                'error' => 'Invalid signature',
            ], Response::HTTP_FORBIDDEN));
            return;
        }
    }

    private function createSignature(string $id, int $timestamp, string $body): ?string
    {
        $signingKey = $this->signingKey;
        if (str_starts_with($signingKey, self::SYMMETRIC_PREFIX)) {
            $signingKey = substr($signingKey, strlen(self::SYMMETRIC_PREFIX));
        }
        $signingKey = base64_decode($signingKey);
        if ($signingKey === false) {
            return null;
        }

        $toSign = "{$id}.{$timestamp}.{$body}";
        $hash = hash_hmac('sha256', $toSign, $signingKey);
        $signature = base64_encode(pack("H*", $hash));

        return "v1,{$signature}";
    }
}
