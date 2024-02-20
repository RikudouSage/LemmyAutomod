<?php

namespace App\Listener;

use App\Message\RemoveOldRowsMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onHttpRequest')]
final readonly class TriggerCleanupListener
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function onHttpRequest(): void
    {
        if (random_int(0, 100) === 50) {
            $this->messageBus->dispatch(new RemoveOldRowsMessage());
        }
    }
}
