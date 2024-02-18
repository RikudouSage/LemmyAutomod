<?php

namespace App\Automod\ModAction\Notification;

use App\Automod\Enum\AutomodPriority;
use App\Automod\ModAction\ModAction;
use App\Context\Context;
use App\Dto\Model\LocalUser;
use App\Enum\FurtherAction;
use App\Enum\RunConfiguration;
use App\Service\InstanceLinkConverter;
use App\Service\Notification\NotificationSender;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentReportView;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostReportView;
use Rikudou\LemmyApi\Response\View\PostView;
use Rikudou\LemmyApi\Response\View\RegistrationApplicationView;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ModAction<CommentView|PostView|Person|RegistrationApplicationView>
 */
#[AsTaggedItem(priority: AutomodPriority::Notification->value)]
final readonly class NotifyOfActionTaken implements ModAction
{
    public function __construct(
        #[Autowire('%app.lemmy.instance%')]
        private string $instance,
        private InstanceLinkConverter $linkConverter,
        private NotificationSender $notificationSender,
        private LemmyApi $api,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        return ($object instanceof CommentView || $object instanceof PostView || $object instanceof Person || $object instanceof RegistrationApplicationView)
            && $this->notificationSender->hasEnabledChannels()
        ;
    }

    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        if (!count($context->getMessages())) {
            return FurtherAction::CanContinue;
        }
        $target = null;
        $username = null;
        if ($object instanceof PostView) {
            $communityHost = parse_url($object->community->actorId, PHP_URL_HOST);
            $community = "[!{$object->community->name}@{$communityHost}]({$this->linkConverter->convertCommunityLink($object->community)})";
            $target = "[this post]({$this->linkConverter->convertPostLink($object->post)}) in {$community}";
            $username = "{$object->creator->name}@" . parse_url($object->creator->actorId, PHP_URL_HOST);
        } elseif ($object instanceof CommentView) {
            $communityHost = parse_url($object->community->actorId, PHP_URL_HOST);
            $community = "[!{$object->community->name}@{$communityHost}]({$this->linkConverter->convertCommunityLink($object->community)})";
            $target = "[this comment]({$this->linkConverter->convertCommentLink($object->comment)}) on [this post]({$this->linkConverter->convertPostLink($object->post)}) in {$community}";
            $username = "{$object->creator->name}@" . parse_url($object->creator->actorId, PHP_URL_HOST);
        } elseif ($object instanceof Person) {
            $target = "their profile";
            $username = "{$object->name}@" . parse_url($object->actorId, PHP_URL_HOST);
        } elseif ($object instanceof RegistrationApplicationView) {
            $target = 'their registration application';
            $username = "{$object->creator->name}@" . parse_url($object->creator->actorId, PHP_URL_HOST);
        } elseif ($object instanceof CommentReportView) {
            $communityHost = parse_url($object->community->actorId, PHP_URL_HOST);
            $community = "[!{$object->community->name}@{$communityHost}]({$this->linkConverter->convertCommunityLink($object->community)})";
            $target = "[this comment]({$this->linkConverter->convertCommentLink($object->comment)}) on [this post]({$this->linkConverter->convertPostLink($object->post)}) in {$community}";
            $username = "{$object->commentCreator->name}@" . parse_url($object->commentCreator->actorId, PHP_URL_HOST);
        } elseif ($object instanceof PostReportView) {
            $communityHost = parse_url($object->community->actorId, PHP_URL_HOST);
            $community = "[!{$object->community->name}@{$communityHost}]({$this->linkConverter->convertCommunityLink($object->community)})";
            $target = "[this post]({$this->linkConverter->convertPostLink($object->post)}) in {$community}";  $username = "{$object->postCreator->name}@" . parse_url($object->postCreator->actorId, PHP_URL_HOST);
        } elseif ($object instanceof LocalUser) {
            $person = $this->api->user()->get($object->personId);
            $target = 'their local account';
            $username = "{$person->name}@" . parse_url($person->actorId, PHP_URL_HOST);
        }

        if ($target === null || $username === null) {
            return FurtherAction::CanContinue;
        }

        $message = "Actions have been taken against [{$username}](https://{$this->instance}/u/{$username}) for {$target}:\n\n";

        foreach ($context->getMessages() as $contextMessage) {
            $message .= " - {$contextMessage}\n";
        }

        $this->notificationSender->sendNotificationAsync($message);
        return FurtherAction::CanContinue;
    }

    public function getRunConfiguration(): RunConfiguration
    {
        return RunConfiguration::Always;
    }
}
