<?php

namespace App\Automod\ModAction\Notification;

use App\Automod\ModAction\ModAction;
use App\Enum\FurtherAction;
use App\Enum\RunConfiguration;
use App\Service\InstanceLinkConverter;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ModAction<CommentView|PostView|Person>
 */
#[AsTaggedItem(priority: -1_000_000)]
final readonly class NotifyAdminAction implements ModAction
{
    /**
     * @param array<string> $adminsToNotify
     */
    public function __construct(
        private LemmyApi $api,
        #[Autowire('%app.lemmy.notify_admins%')]
        private array $adminsToNotify,
        #[Autowire('%app.lemmy.instance%')]
        private string $instance,
        private InstanceLinkConverter $linkConverter,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        return $object instanceof CommentView || $object instanceof PostView || $object instanceof Person;
    }

    public function takeAction(object $object, array $previousActions = []): FurtherAction
    {
        if (!$this->adminsToNotify) {
            return FurtherAction::CanContinue;
        }
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

        foreach ($this->adminsToNotify as $adminUsername) {
            $this->api->currentUser()->sendPrivateMessage(
                $this->api->user()->get($adminUsername),
                $message,
            );
        }

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
