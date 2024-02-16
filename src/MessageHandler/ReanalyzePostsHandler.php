<?php

namespace App\MessageHandler;

use App\Message\AnalyzePostMessage;
use App\Message\ReanalyzePostsMessage;
use DateTimeInterface;
use Rikudou\LemmyApi\Enum\ListingType;
use Rikudou\LemmyApi\Enum\SortType;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\View\PostView;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
final readonly class ReanalyzePostsHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private LemmyApi $api,
    ) {
    }

    public function __invoke(ReanalyzePostsMessage $message): void
    {
        foreach ($this->getPosts($message->since) as $post) {
            $this->messageBus->dispatch(new AnalyzePostMessage($post->post->id), [
                new DispatchAfterCurrentBusStamp(),
            ]);
        }
    }

    /**
     * @return iterable<PostView>
     */
    private function getPosts(DateTimeInterface $until): iterable
    {
        $page = 1;
        do {
            $posts = $this->api->post()->getPosts(page: $page, sort: SortType::New, listingType: ListingType::All);
            foreach ($posts as $post) {
                yield $post;
            }
            ++$page;
        } while (isset($post) && $post->post->published > $until);
    }
}
