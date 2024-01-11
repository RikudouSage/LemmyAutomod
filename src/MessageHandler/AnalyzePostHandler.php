<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Message\AnalyzePostMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AnalyzePostHandler
{
    public function __construct(
        private LemmyApi $api,
        private Automod $automod,
    ) {
    }

    public function __invoke(AnalyzePostMessage $message): void
    {
        $post = $this->api->post()->get($message->postId);
        $this->automod->analyze($post);
    }
}
