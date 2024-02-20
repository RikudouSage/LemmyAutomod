<?php

namespace App\MessageHandler;

use App\Message\RestoreCommentMessage;
use App\Message\RestorePostMessage;
use App\Message\UnbanUserMessage;
use App\Repository\RemovalLogRepository;
use App\Service\LemmyHelper;
use DateInterval;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Comment;
use Rikudou\LemmyApi\Response\Model\Post;
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
        private ?DateInterval $removalLogValidity,
        private RemovalLogRepository $removalLogRepository,
    ) {
    }

    public function __invoke(UnbanUserMessage $message): void
    {
        $user = $this->api->user()->get("{$message->username}@{$message->instance}");

        $this->api->admin()->unbanUser($user, reason: 'Mistakenly banned by Automod');
        foreach ($this->lemmyHelper->getUserPosts($user) as $post) {
            if (!$this->shouldRestore($post->post)) {
                continue;
            }
            $this->messageBus->dispatch(new RestorePostMessage($post->post->id), [
                new DispatchAfterCurrentBusStamp(),
            ]);
        }
        foreach ($this->lemmyHelper->getUserComments($user) as $comment) {
            if (!$this->shouldRestore($comment->comment)) {
                continue;
            }
            $this->messageBus->dispatch(new RestoreCommentMessage($comment->comment->id), [
                new DispatchAfterCurrentBusStamp(),
            ]);
        }
    }

    private function shouldRestore(Comment|Post $target): bool
    {
        if ($this->removalLogValidity === null) {
            return true;
        }

        return $this->removalLogRepository->findOneBy([
            'type' => $target instanceof Post ? 'post' : 'comment',
            'targetId' => $target->id,
        ]) !== null;
    }
}
