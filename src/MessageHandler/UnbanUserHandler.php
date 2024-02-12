<?php

namespace App\MessageHandler;

use App\Message\RestoreCommentMessage;
use App\Message\RestorePostMessage;
use App\Message\UnbanUserMessage;
use App\Service\LemmyHelper;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
final readonly class UnbanUserHandler
{
    public function __construct(
        private LemmyApi $api,
        private LemmyHelper $lemmyHelper,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(UnbanUserMessage $message): void
    {
        $user = $this->api->user()->get("{$message->username}@{$message->instance}");

        $this->api->admin()->unbanUser($user, reason: 'Mistakenly banned by Automod');
        foreach ($this->lemmyHelper->getUserPosts($user) as $post) {
            $this->messageBus->dispatch(new RestorePostMessage($post->post->id), [
                new DispatchAfterCurrentBusStamp(),
            ]);
        }
        foreach ($this->lemmyHelper->getUserComments($user) as $comment) {
            $this->messageBus->dispatch(new RestoreCommentMessage($comment->comment->id), [
                new DispatchAfterCurrentBusStamp(),
            ]);
        }
    }
}
