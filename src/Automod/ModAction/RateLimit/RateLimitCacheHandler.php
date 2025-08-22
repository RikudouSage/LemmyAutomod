<?php

namespace App\Automod\ModAction\RateLimit;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Enum\FurtherAction;
use Doctrine\Common\Cache\Psr6\CacheItem;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use ReflectionClass;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\RateLimiter\RateLimit;

/**
 * @extends AbstractModAction<CommentView|PostView>
 */
final readonly class RateLimitCacheHandler extends AbstractModAction
{
    public function __construct(
        #[Autowire('%app.rate_limit.comments%')]
        private int          $commentsLimit,
        #[Autowire('%app.rate_limit.posts%')]
        private int          $postsLimit,
        #[Autowire('%app.rate_limit.use_cache%')]
        private bool $useCache,
        private CacheItemPoolInterface $cache,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        return $this->useCache && ($object instanceof CommentView || $object instanceof PostView);
    }

    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        $cacheItem = $this->getCacheItem($object);
        $items = $cacheItem->get();

        $limit = $object instanceof PostView ? $this->postsLimit : $this->commentsLimit;
        array_unshift($items, $object);
        if (count($items) > $limit) {
            $items = array_slice($items, 0, $limit);
        }
        $cacheItem->set($items);
        $this->cache->save($cacheItem);

        return FurtherAction::CanContinue;
    }

    /**
     * @param CommentView|PostView $object
     * @throws InvalidArgumentException
     */
    public function getCacheItem(object $object): CacheItemInterface
    {
        $shortName = new ReflectionClass($object)->getShortName();
        $person = $object->creator;
        $cacheItem = $this->cache->getItem("rate_limit.{$shortName}.{$person->id}");
        if (!$cacheItem->isHit()) {
            $cacheItem->set([]);
        }

        return $cacheItem;
    }
}
