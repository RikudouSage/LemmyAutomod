<?php

namespace App\Automod\ModAction\Notification;

use App\Automod\Enum\AutomodPriority;
use App\Automod\ModAction\ModAction;
use App\Enum\FurtherAction;
use App\Enum\RunConfiguration;
use App\Service\InstanceLinkConverter;
use App\Service\Notification\NotificationSender;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ModAction<CommentView|PostView|Person>
 */
#[AsTaggedItem(priority: AutomodPriority::Notification->value)]
final readonly class NotifyOfActionTaken implements ModAction
{
    public function __construct(
        #[Autowire('%app.lemmy.instance%')]
        private string $instance,
        private InstanceLinkConverter $linkConverter,
        private NotificationSender $notificationSender,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        return ($object instanceof CommentView || $object instanceof PostView || $object instanceof Person)
            && $this->notificationSender->hasEnabledChannels()
        ;
    }

    public function takeAction(object $object, array $previousActions = []): FurtherAction
    {
        if (!count($previousActions)) {
            return FurtherAction::CanContinue;
        }
        $username = "{$object->creator->name}@" . parse_url($object->creator->actorId, PHP_URL_HOST);
        $target = null;
        if ($object instanceof PostView) {
            $target = $this->linkConverter->convertPostLink($object->post);
        } elseif ($object instanceof CommentView) {
            $target = $this->linkConverter->convertCommentLink($object->comment);
        } elseif ($object instanceof Person) {
            $target = $this->linkConverter->convertPersonLink($object);
        }

        if ($target === null) {
            return FurtherAction::CanContinue;
        }

        $actionNames = array_map(
            fn (ModAction $action) => $action->getDescription(),
            array_filter($previousActions, fn (ModAction $action) => $action->getDescription() !== null),
        );

        $message = "Actions have been taken against [{$username}](https://{$this->instance}/u/{$username}) for {$target}:\n\n";

        foreach ($actionNames as $actionName) {
            $message .= " - {$actionName}\n";
        }

        $this->notificationSender->sendNotification($message);
        return FurtherAction::CanContinue;
    }

    public function getRunConfiguration(): RunConfiguration
    {
        return RunConfiguration::Always;
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
