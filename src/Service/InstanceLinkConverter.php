<?php

namespace App\Service;

use LogicException;
use Rikudou\LemmyApi\Exception\LemmyApiException;
use Rikudou\LemmyApi\LemmyApi;
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
}
