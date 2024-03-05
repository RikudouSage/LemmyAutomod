<?php

namespace App\Automod\ModAction\Notification;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Entity\WatchedUser;
use App\Enum\FurtherAction;
use App\Repository\WatchedUserRepository;
use App\Service\InstanceLinkConverter;
use App\Service\Notification\NotificationSender;
use App\Service\UserEntityResolver;
use LogicException;
use Override;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentReportView;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostReportView;
use Rikudou\LemmyApi\Response\View\PostView;
use Rikudou\LemmyApi\Response\View\PrivateMessageReportView;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @extends AbstractModAction<PostView|CommentView|CommentReportView|PostReportView|PrivateMessageReportView>
 */
final readonly class NotifyAboutWatchedUserAction extends AbstractModAction
{
    public function __construct(
        private WatchedUserRepository $watchedUserRepository,
        private UserEntityResolver $userEntityResolver,
        private NotificationSender $notificationSender,
        #[Autowire('%app.lemmy.instance%')]
        private string $instance,
        private InstanceLinkConverter $linkConverter,
    ) {
    }

    #[Override]
    public function shouldRun(object $object): bool
    {
        if (
            !$object instanceof PostView
            && !$object instanceof CommentView
            && !$object instanceof CommentReportView
            && !$object instanceof PostReportView
            && !$object instanceof PrivateMessageReportView
        ) {
            return false;
        }

        $watchedUsers = $this->watchedUserRepository->findBy(['enabled' => true]);
        if (!count($watchedUsers)) {
            return false;
        }
        $this->userEntityResolver->resolve(...$watchedUsers);

        $person = $this->getPerson($object);
        if (!count(array_filter($watchedUsers, fn (WatchedUser $user) => $user->getUserId() === $person->id))) {
            return false;
        }

        return true;
    }

    #[Override]
    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        $person = $this->getPerson($object);
        $username = "{$person->name}@" . parse_url($person->actorId, PHP_URL_HOST);

        $message = "New action by a watched user ([{$username}](https://{$this->instance}/u/{$username})): ";
        if ($object instanceof PostView) {
            $message .= "They've created a new post: " . $this->linkConverter->convertPostLink($object->post);
        } else if ($object instanceof CommentView) {
            $message .= "They've created a new comment: " . $this->linkConverter->convertCommentLink($object->comment);
        } else if ($object instanceof CommentReportView) {
            $message .= "Their comment has been reported: " . $this->linkConverter->convertCommentLink($object->comment);
        } else if ($object instanceof PostReportView) {
            $message .= "Their post has been reported: " . $this->linkConverter->convertPostLink($object->post);
        } else if ($object instanceof PrivateMessageReportView) {
            $message .= "Their private message has been reported.";
        } else {
            throw new LogicException('Unsupported object: ' . get_class($object));
        }

        $this->notificationSender->sendNotificationAsync($message);

        return FurtherAction::CanContinue;
    }

    private function getPerson(PostView|CommentView|CommentReportView|PostReportView|PrivateMessageReportView $object): Person
    {
        if (
            $object instanceof PostView
            || $object instanceof CommentView
        ) {
            return $object->creator;
        }

        if ($object instanceof CommentReportView) {
            return $object->commentCreator;
        }
        if ($object instanceof PostReportView) {
            return $object->postCreator;
        }
        if ($object instanceof PrivateMessageReportView) {
            return $object->privateMessageCreator;
        }

        throw new LogicException('Unhandled case: ' . get_class($object));
    }
}
