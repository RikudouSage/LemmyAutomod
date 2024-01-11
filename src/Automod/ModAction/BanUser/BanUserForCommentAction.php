<?php

namespace App\Automod\ModAction\BanUser;

use Rikudou\LemmyApi\Response\View\CommentView;

/**
 * @extends AbstractBanUserModAction<CommentView>
 */
final readonly class BanUserForCommentAction extends AbstractBanUserModAction
{
    public function shouldRun(object $object): bool
    {
        return $object instanceof CommentView && parent::shouldRun($object);
    }

    protected function getTextsToCheck(object $object): array
    {
        return [$object->comment->content, $object->creator->name, $object->creator->displayName];
    }
}
