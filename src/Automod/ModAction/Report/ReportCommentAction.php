<?php

namespace App\Automod\ModAction\Report;

use App\Helper\TextsHelper;
use Rikudou\LemmyApi\Response\View\CommentView;

/**
 * @extends AbstractReportAction<CommentView>
 */
final readonly class ReportCommentAction extends AbstractReportAction
{
    public function shouldRun(object $object): bool
    {
        return $object instanceof CommentView && parent::shouldRun($object);
    }

    protected function getTextsToCheck(object $object): array
    {
        return TextsHelper::getCommentTextsToCheck($object);
    }

    protected function report(object $object, string $message): void
    {
        $this->api->comment()->report($object->comment, $message);
    }
}
