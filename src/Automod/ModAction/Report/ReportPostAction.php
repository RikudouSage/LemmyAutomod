<?php

namespace App\Automod\ModAction\Report;

use App\Automod\ModAction\AbstractModAction;
use App\Enum\FurtherAction;
use App\Helper\TextsHelper;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;

/**
 * @extends AbstractReportAction<PostView>
 */
final readonly class ReportPostAction extends AbstractReportAction
{
    public function shouldRun(object $object): bool
    {
        return $object instanceof PostView && parent::shouldRun($object);
    }

    protected function getTextsToCheck(object $object): array
    {
        return TextsHelper::getPostTextsToCheck($object);
    }

    protected function report(object $object, string $message): void
    {
        $this->api->post()->report($object->post, $message);
    }
}
