<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Message\AnalyzeLocalUserMessage;
use App\Message\AnalyzeUserMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
final readonly class AnalyzeLocalUserHandler
{
    public function __construct(
        private Automod $automod,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(AnalyzeLocalUserMessage $message): void
    {
        $this->automod->analyze($message->localUser);
        $this->messageBus->dispatch(new AnalyzeUserMessage($message->localUser->personId), [
            new DispatchAfterCurrentBusStamp(),
        ]);
    }
}
