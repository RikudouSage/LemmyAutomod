<?php

namespace App\Automod\ModAction\BanUser;

use App\Helper\TextsHelper;
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
        return TextsHelper::getCommentTextsToCheck($object);
    }
}
