<?php

namespace App\MessageHandler;

use App\Message\RestoreCommentMessage;
use App\Message\RestorePostMessage;
use App\Repository\RemovalLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RestoreCommentHandler
{
    public function __construct(
        private LemmyApi $api,
        private RemovalLogRepository $removalLogRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(RestoreCommentMessage $message): void
    {
        $this->api->moderator()->restoreRemovedComment($message->commentId);
        if ($entity = $this->removalLogRepository->findOneBy(['type' => 'comment', 'targetId' => $message->commentId])) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }
}
