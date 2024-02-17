<?php

namespace App\Automod\ModAction\Notification;

use App\Automod\ModAction\ModAction;
use App\Context\Context;
use App\Enum\FurtherAction;
use App\Enum\RunConfiguration;
use App\Service\Notification\NotificationSender;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ModAction<CommentView|PostView>
 */
final readonly class NotifyOnFirstPostOrComment implements ModAction
{
    public function __construct(
        #[Autowire('%app.notify.first_post_comment%')]
        private bool $enabled,
        private LemmyApi $api,
        private NotificationSender $notificationSender,
        #[Autowire('%app.lemmy.instance%')]
        private string $instance,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        if (!$this->notificationSender->hasEnabledChannels()) {
            return false;
        }
        if (!$this->enabled) {
            return false;
        }
        if (!$object instanceof CommentView && !$object instanceof PostView) {
            return false;
        }
        if (!$object->creator->local) {
            return false;
        }

        $counts = $this->api->user()->getCounts($object->creator);
        if ($object instanceof CommentView && $counts->commentCount === 1) {
            return true;
        }
        if ($object instanceof PostView && $counts->postCount === 1) {
            return true;
        }

        return false;
    }

    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        if ($object instanceof CommentView) {
            $this->notificationSender->sendNotificationAsync("User's first comment: https://{$this->instance}/comment/{$object->comment->id}");
        }
        if ($object instanceof PostView) {
            $this->notificationSender->sendNotificationAsync("User's first post: https://{$this->instance}/post/{$object->post->id}");
        }

        return FurtherAction::CanContinue;
    }

    public function getRunConfiguration(): RunConfiguration
    {
        return RunConfiguration::Always;
    }
}
