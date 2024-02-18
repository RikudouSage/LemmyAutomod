<?php

namespace App\Automod\ModAction\Notification;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Enum\FurtherAction;
use App\Service\Notification\NotificationSender;
use Override;
use Rikudou\LemmyApi\Response\View\CommentReportView;
use Rikudou\LemmyApi\Response\View\PostReportView;
use Rikudou\LemmyApi\Response\View\PrivateMessageReportView;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @extends AbstractModAction<CommentReportView|PostReportView|PrivateMessageReportView>
 */
final readonly class NotifyOfANewReportAction extends AbstractModAction
{
    public function __construct(
        #[Autowire('%app.notify.reports%')]
        private bool $enabled,
        private NotificationSender $notificationSender,
    ) {
    }

    #[Override]
    public function shouldRun(object $object): bool
    {
        if (!$this->enabled) {
            return false;
        }

        return $object instanceof CommentReportView || $object instanceof PostReportView || $object instanceof PrivateMessageReportView;
    }

    #[Override]
    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        $keyword = '';
        if ($object instanceof CommentReportView) {
            $keyword = 'comment';
        } elseif ($object instanceof PostReportView) {
            $keyword = 'post';
        } elseif ($object instanceof PrivateMessageReportView) {
            $keyword = 'private message';
        } else {
            return FurtherAction::CanContinue;
        }
        $this->notificationSender->sendNotificationAsync("There's a new {$keyword} report.");

        return FurtherAction::CanContinue;
    }
}
