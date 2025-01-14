<?php

namespace App\Listener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onRequest')]
final readonly class PreRequestListener
{
    public function __construct(
        #[Autowire('%app.management_api.enabled%')]
        private bool $managementApiEnabled,
        #[Autowire('%rikudou_api.api_prefix%')]
        private string $apiPrefix,
    ) {
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getpathInfo(), $this->apiPrefix)) {
            return;
        }

        if ($this->managementApiEnabled) {
            return;
        }

        $event->setResponse(new JsonResponse([], Response::HTTP_NOT_FOUND));
    }
}
