<?php

namespace App\Service;

use App\Message\RemovePostMessage;
use Rikudou\LemmyApi\Enum\SortType;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final readonly class LemmyHelper
{
    public function __construct(
        private LemmyApi $api,
    ) {
    }

    /**
     * @return iterable<PostView>
     */
    public function getUserPosts(Person $user): iterable
    {
        $page = 1;
        do {
            $posts = $this->api->user()->getPosts($user, page: $page, sort: SortType::New);
            yield from $posts;
            ++$page;
        } while (count($posts));
    }

    /**
     * @return iterable<CommentView>
     */
    public function getUserComments(Person $user): iterable
    {
        $page = 1;
        do {
            $posts = $this->api->user()->getComments($user, page: $page, sort: SortType::New);
            yield from $posts;
            ++$page;
        } while (count($posts));
    }
}
