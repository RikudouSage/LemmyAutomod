<?php

namespace App\Automod\ModAction\UserReport;

use App\Automod\ModAction\AbstractModAction;
use App\Entity\TrustedUser;
use App\Enum\FurtherAction;
use App\Repository\TrustedUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Rikudou\LemmyApi\Response\View\CommentReportView;
use Rikudou\LemmyApi\Response\View\PostReportView;

/**
 * @extends AbstractModAction<CommentReportView|PostReportView>
 */
final readonly class RemoveReportedFromTrustedUserModAction extends AbstractModAction
{
    public function __construct(
        private TrustedUserRepository $trustedUserRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        if (!$object instanceof CommentReportView && !$object instanceof PostReportView) {
            return false;
        }

        $trustedIds = array_map(function (TrustedUser $user) {
            if (!$user->getUserId()) {
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

    public function takeAction(object $object, array $previousActions = []): FurtherAction
    {
        if ($object instanceof CommentReportView) {
            $this->api->moderator()->removeComment($object->comment, $object->commentReport->reason);
            $this->api->moderator()->resolveCommentReport($object->commentReport);
        }
        if ($object instanceof PostReportView) {
            $this->api->moderator()->removePost($object->post, $object->postReport->reason);
            $this->api->moderator()->resolvePostReport($object->postReport);
        }

        return FurtherAction::CanContinue;
    }

    public function getDescription(): ?string
    {
        return 'the content has been deleted';
    }
}
