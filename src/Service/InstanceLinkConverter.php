<?php

namespace App\Service;

use LogicException;
use Rikudou\LemmyApi\Exception\LemmyApiException;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Comment;
use Rikudou\LemmyApi\Response\Model\Community;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\Model\Post;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class InstanceLinkConverter
{
    public function __construct(
        #[Autowire('%app.lemmy.instance%')]
        private string $instance,
        private LemmyApi $api,
    ) {
    }

    public function convertPostLink(Post $post): string
    {
        $link = $post->apId;
        $linkHost = parse_url($link, PHP_URL_HOST);
        if ($linkHost === $this->instance) {
            return $link;
        }

        try {
            $localPost = $this->api->miscellaneous()->resolveObject($link)?->post
                ?? throw new LogicException("Could not resolve local post for '{$link}'");
        } catch (LemmyApiException) {
            return $link;
        }

        return "https://{$this->instance}/post/{$localPost->post->id}";
    }

    public function convertCommentLink(Comment $comment): string
    {
        $link = $comment->apId;
        $linkHost = parse_url($link, PHP_URL_HOST);
        if ($linkHost === $this->instance) {
            return $link;
        }

        try {
            $localComment = $this->api->miscellaneous()->resolveObject($link)?->comment
                ?? throw new LogicException("Could not resolve local comment for '{$link}'");
        } catch (LemmyApiException) {
            return $link;
        }

        return "https://{$this->instance}/comment/{$localComment->comment->id}";
    }

    public function convertPersonLink(Person $person): string
    {
        $link = $person->actorId;
        $linkHost = parse_url($link, PHP_URL_HOST);
        if ($linkHost === $this->instance) {
            return $link;
        }

        return "https://{$this->instance}/u/{$person->name}@{$linkHost}";
    }

    public function convertCommunityLink(Community $community): string
    {
        $link = $community->actorId;
        $linkHost = parse_url($link, PHP_URL_HOST);
        if ($linkHost === $this->instance) {
            return $link;
        }

        return "https://{$this->instance}/c/{$community->name}@{$linkHost}";
    }
}
