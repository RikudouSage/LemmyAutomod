<?php

namespace App\Automod\ModAction\Notification;

use App\Automod\Enum\AutomodPriority;
use App\Automod\ModAction\ModAction;
use App\Enum\FurtherAction;
use App\Enum\RunConfiguration;
use App\Service\Notification\NotificationSender;
use Rikudou\LemmyApi\Response\Model\Person;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ModAction<Person>
 */
#[AsTaggedItem(priority: AutomodPriority::Notification->value)]
final readonly class NotifyOnNewLocalUser implements ModAction
{
    public function __construct(
        #[Autowire('%app.notify.new_users%')]
        private bool $enabled,
        private NotificationSender $notificationSender,
        #[Autowire('%app.lemmy.instance%')]
        private string $instance,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        return $this->enabled && $object instanceof Person && $object->local && $this->notificationSender->hasEnabledChannels();
    }

    public function takeAction(object $object, array $previousActions = []): FurtherAction
    {
        $this->notificationSender->sendNotification(
            "New user has been added: [{$object->name}](https://{$this->instance}/u/{$object->name})",
        );

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
