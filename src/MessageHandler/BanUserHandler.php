<?php

namespace App\MessageHandler;

use App\Message\BanUserMessage;
use App\Message\RemoveCommentMessage;
use App\Message\RemovePostMessage;
use Rikudou\LemmyApi\Enum\SortType;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Person;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
final readonly class BanUserHandler
{
    public function __construct(
        private LemmyApi $api,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(BanUserMessage $message): void
    {
        $this->api->admin()->banUser(user: $message->user, reason: $message->reason, removeData: false);
        if ($message->removePosts) {
            $this->deletePostsFederated($message->user);
        }
        if ($message->removeComments) {
            $this->deleteCommentsFederated($message->user);
        }
    }

    private function deletePostsFederated(Person $user): void
    {
        $page = 1;
        do {
            $posts = $this->api->user()->getPosts($user, page: $page, sort: SortType::New);
            foreach ($posts as $post) {
                $this->messageBus->dispatch(new RemovePostMessage($post->post->id), [
                    new DispatchAfterCurrentBusStamp(),
                ]);
            }
            ++$page;
        } while (count($posts));
    }

    private function deleteCommentsFederated(Person $user): void
    {
        $page = 1;
        do {
            $comments = $this->api->user()->getComments($user, page: $page, sort: SortType::New);
            foreach ($comments as $comment) {
                $this->messageBus->dispatch(new RemoveCommentMessage($comment->comment->id), [
                    new DispatchAfterCurrentBusStamp(),
                ]);
            }
            ++$page;
        } while (count($comments));
    }
}
