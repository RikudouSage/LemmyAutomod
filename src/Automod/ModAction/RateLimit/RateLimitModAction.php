<?php

namespace App\Automod\ModAction\RateLimit;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Enum\FurtherAction;
use App\Helper\DateIntervalHelper;
use App\Message\BanUserMessage;
use Closure;
use DateInterval;
use DateTimeInterface;
use Rikudou\LemmyApi\Enum\SortType;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

/**
 * @extends AbstractModAction<PostView|CommentView>
 */
final readonly class RateLimitModAction extends AbstractModAction
{
    public function __construct(
        #[Autowire('%app.rate_limit.comments%')]
        private int          $commentsLimit,
        #[Autowire('%app.rate_limit.posts%')]
        private int                   $postsLimit,
        #[Autowire('%app.rate_limit.period%')]
        private DateInterval          $rateLimitPeriod,
        #[Autowire('%app.rate_limit.ban_duration%')]
        private DateInterval          $banDuration,
        #[Autowire('%app.rate_limit.use_cache%')]
        private bool                  $useCache,
        #[Autowire('%app.rate_limit.remove_content%')]
        private bool                  $removeContent,
        private MessageBusInterface   $messageBus,
        private RateLimitCacheHandler $cacheHandler,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        if ($this->commentsLimit <= 0 && $this->postsLimit <= 0) {
            return false;
        }

        if (!$object instanceof PostView && !$object instanceof CommentView) {
            return false;
        }

        return true;
    }

    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        if ($object instanceof PostView && $this->postsLimit > 0) {
            return $this->handlePost($object, $context);
        } else if ($object instanceof CommentView && $this->commentsLimit > 0) {
            return $this->handleComment($object, $context);
        }

        return FurtherAction::CanContinue;
    }

    private function handlePost(PostView $object, Context $context): FurtherAction
    {
        return $this->handle(
            person: $object->creator,
            allContentGetter: function (Person $person) use ($object) {
                if ($this->useCache) {
                    return $this->cacheHandler->getCacheItem($object)->get();
                }

                $allPosts = [];

                do {
                    $page ??= 1;
                    $currentPosts = $this->api->user()->getPosts(user: $person, limit: 10, page: $page, sort: SortType::New);
                    ++$page;
                    $allPosts = [...$allPosts, ...$currentPosts];
                } while (count($currentPosts) && count($allPosts) < $this->postsLimit);

                return $allPosts;
            },
            limit: $this->postsLimit,
            published: static fn (PostView $post) => $post->post->published,
            reason: 'too many posts',
            itemTypeName: 'post',
            context: $context,
        );
    }

    private function handleComment(CommentView $object, Context $context): FurtherAction
    {
        return $this->handle(
            person: $object->creator,
            allContentGetter: function (Person $person) use ($object) {
                if ($this->useCache) {
                    return $this->cacheHandler->getCacheItem($object)->get();
                }

                $allComments = [];

                do {
                    $page ??= 1;
                    $currentComments = $this->api->user()->getComments(user: $person, limit: 20, page: $page, sort: SortType::New);
                    ++$page;
                    $allComments = [...$allComments, ...$currentComments];
                } while (count($currentComments) && count($allComments) < $this->commentsLimit);

                return $allComments;
            },
            limit: $this->commentsLimit,
            published: static fn (CommentView $comment) => $comment->comment->published,
            reason: 'too many comments',
            itemTypeName: 'comment',
            context: $context,
        );
    }

    /**
     * @template TType of CommentView|PostView
     * @param Closure(Person): array<TType> $allContentGetter
     * @param Closure(TType): DateTimeInterface $published
     * @throws ExceptionInterface
     */
    private function handle(
        Person $person,
        Closure $allContentGetter,
        int $limit,
        Closure $published,
        string $reason,
        string $itemTypeName,
        Context $context,
    ): FurtherAction {
        $allContent = $allContentGetter($person);

        if (count($allContent) < $limit) {
            return FurtherAction::CanContinue;
        }

        $allContent = array_slice($allContent, 0, $limit);

        $newest = $allContent[0];
        $oldest = $allContent[count($allContent) - 1];

        $diff = $published($newest)->diff($published($oldest));
        if (DateIntervalHelper::compare($diff, $this->rateLimitPeriod) <= 0) {
            $this->messageBus->dispatch(new BanUserMessage(
                user: $person,
                reason: $reason,
                removePosts: $this->removeContent,
                removeComments: $this->removeContent,
                duration: $this->banDuration,
            ), [
                new DispatchAfterCurrentBusStamp(),
            ]);
            $limitPeriod = DateIntervalHelper::toString($this->rateLimitPeriod);
            $context->addMessage("banned for breaching the {$itemTypeName} rate limit threshold ({$limit} in {$limitPeriod})");

            return FurtherAction::CanContinue;
        }

        return FurtherAction::CanContinue;
    }
}
