<?php

namespace App\Automod\ModAction\UserReport;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Entity\TrustedUser;
use App\Enum\FurtherAction;
use App\Repository\TrustedUserRepository;
use App\Service\InstanceLinkConverter;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Rikudou\LemmyApi\Response\View\CommentReportView;
use Rikudou\LemmyApi\Response\View\PostReportView;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @extends AbstractModAction<CommentReportView|PostReportView>
 */
final readonly class RemoveReportedFromTrustedUserModAction extends AbstractModAction
{
    public function __construct(
        private TrustedUserRepository $trustedUserRepository,
        private EntityManagerInterface $entityManager,
        private InstanceLinkConverter $linkConverter,
        #[Autowire('%app.lemmy.instance%')]
        private string $instance,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        if (!$object instanceof CommentReportView && !$object instanceof PostReportView) {
            return false;
        }

        $trustedIds = array_map(function (TrustedUser $user) {
            if ($user->getUserId()) {
                return $user->getUserId();
            }

            if ($user->getUsername() && $user->getInstance()) {
                $id = $this->api->user()->get("{$user->getUsername()}@{$user->getInstance()}")->id;
                $user->setUserId($id);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user->getUserId();
            }

            throw new LogicException('The user must either have an ID or username and instance');
        }, $this->trustedUserRepository->findAll());

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
            $this->api->moderator()->removeComment($object->comment, $object->commentReport->reason);
            $this->api->moderator()->resolveCommentReport($object->commentReport);
        }
        if ($object instanceof PostReportView) {
            $context->addMessage("reported post ({$this->linkConverter->convertPostLink($object->post)}) has been automatically resolved because it was reported by a trusted user ({$reporter})");
            $this->api->moderator()->removePost($object->post, $object->postReport->reason);
            $this->api->moderator()->resolvePostReport($object->postReport);
        }

        return FurtherAction::CanContinue;
    }
}
