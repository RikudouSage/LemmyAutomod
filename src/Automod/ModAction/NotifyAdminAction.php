<?php

namespace App\Automod\ModAction;

use App\Enum\FurtherAction;
use App\Enum\RunConfiguration;
use App\Service\InstanceLinkConverter;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostView;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ModAction<CommentView|PostView>
 */
#[AsTaggedItem(priority: -1_000_000)]
final readonly class NotifyAdminAction implements ModAction
{
    public function __construct(
        private LemmyApi $api,
        #[Autowire('%app.lemmy.notify_admin%')]
        private string $adminToNotify,
        #[Autowire('%app.lemmy.instance%')]
        private string $instance,
        private InstanceLinkConverter $linkConverter,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        return $object instanceof CommentView || $object instanceof PostView;
    }

    public function takeAction(object $object, array $previousActions = []): FurtherAction
    {
        if (!$this->adminToNotify) {
            return FurtherAction::CanContinue;
        }
        if (!count($previousActions)) {
            return FurtherAction::CanContinue;
        }
        $username = "{$object->creator->name}@" . parse_url($object->creator->actorId, PHP_URL_HOST);
        $target = null;
        if ($object instanceof PostView) {
            $target = $this->linkConverter->convertPostLink($object->post);
        }

        $actionNames = array_map(
            fn (ModAction $action) => $action->getDescription(),
            array_filter($previousActions, fn (ModAction $action) => $action->getDescription() !== null),
        );

        $message = "Actions have been taken against [{$username}](https://{$this->instance}/u/{$username}) for {$target}:\n\n";

        foreach ($actionNames as $actionName) {
            $message .= " - {$actionName}\n";
        }

        $this->api->currentUser()->sendPrivateMessage(
            $this->api->user()->get($this->adminToNotify),
            $message,
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
