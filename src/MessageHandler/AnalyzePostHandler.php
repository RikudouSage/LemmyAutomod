<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Message\AnalyzePostMessage;
use App\Service\IgnoredUserManager;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AnalyzePostHandler
{
    public function __construct(
        private LemmyApi $api,
        private Automod $automod,
        private IgnoredUserManager $ignoredUserManager,
    ) {
    }

    public function __invoke(AnalyzePostMessage $message): void
    {
        $post = $this->api->post()->get($message->postId);
        if ($this->ignoredUserManager->shouldBeIgnored($post->creator)) {
            return;
        }
        $this->automod->analyze($post);
    }
}
