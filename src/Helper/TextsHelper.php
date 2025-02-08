<?php

namespace App\Helper;

use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\CommunityView;
use Rikudou\LemmyApi\Response\View\PostView;

final readonly class TextsHelper
{
    /**
     * @return array<string | null>
     */
    public static function getPostTextsToCheck(PostView $post): array
    {
        return [$post->post->name, $post->post->body, $post->post->url, $post->creator->name, $post->creator->displayName];
    }

    /**
     * @return array<string | null>
     */
    public static function getCommentTextsToCheck(CommentView $comment): array
    {
        return [$comment->comment->content, $comment->creator->name, $comment->creator->displayName];
    }

    /**
     * @return array<string | null>
     */
    public static function getCommunityTextsToCheck(CommunityView $community): array
    {
        return [$community->community->name, $community->community->title, $community->community->description];
    }
}
