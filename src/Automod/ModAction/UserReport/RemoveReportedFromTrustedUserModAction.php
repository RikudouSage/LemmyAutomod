<?php

namespace App\Automod\ModAction\UserReport;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Entity\TrustedUser;
use App\Enum\FurtherAction;
use App\Message\RemoveCommentMessage;
use App\Message\RemovePostMessage;
use App\Repository\TrustedUserRepository;
use App\Service\InstanceLinkConverter;
use App\Service\UserEntityResolver;
use Rikudou\LemmyApi\Response\View\CommentReportView;
use Rikudou\LemmyApi\Response\View\PostReportView;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @extends AbstractModAction<CommentReportView|PostReportView>
 */
final readonly class RemoveReportedFromTrustedUserModAction extends AbstractModAction
{
    public function __construct(
        private TrustedUserRepository $trustedUserRepository,
        private InstanceLinkConverter $linkConverter,
        #[Autowire('%app.lemmy.instance%')]
        private string $instance,
        private MessageBusInterface $messageBus,
        private UserEntityResolver $userEntityResolver,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        if (!$object instanceof CommentReportView && !$object instanceof PostReportView) {
            return false;
        }

        $trustedIds = array_map(function (TrustedUser $user) {
            $this->userEntityResolver->resolve($user);
            return $user->getUserId();
        }, $this->trustedUserRepository->findBy(['enabled' => true]));

        return in_array($object->creator->id, $trustedIds, true);
    }

    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        $reporter = sprintf(
            '[%1$s@%2$s](https://%3$s/u/%1$s@%2$s',
            $object->creator->name,
            parse_url($object->creator->actorId, PHP_URL_HOST),
            $this->instance,
        );
        if ($object instanceof CommentReportView) {
            $context->addMessage("reported comment ({$this->linkConverter->convertCommentLink($object->comment)}) has been automatically resolved because it was reported by a trusted user ({$reporter})");
            $this->messageBus->dispatch(new RemoveCommentMessage($object->comment->id));
            $this->api->moderator()->resolveCommentReport($object->commentReport);
        }
        if ($object instanceof PostReportView) {
            $context->addMessage("reported post ({$this->linkConverter->convertPostLink($object->post)}) has been automatically resolved because it was reported by a trusted user ({$reporter})");
            $this->messageBus->dispatch(new RemovePostMessage($object->post->id));
            $this->api->moderator()->resolvePostReport($object->postReport);
        }

        return FurtherAction::CanContinue;
    }
}
