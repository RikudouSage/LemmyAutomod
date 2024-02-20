<?php

namespace App\MessageHandler;

use App\Message\RestorePostMessage;
use App\Repository\RemovalLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RestorePostHandler
{
    public function __construct(
        private LemmyApi $api,
        private RemovalLogRepository $removalLogRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(RestorePostMessage $message): void
    {
        $this->api->moderator()->restoreRemovedPost($message->postId);
        if ($entity = $this->removalLogRepository->findOneBy(['type' => 'post', 'targetId' => $message->postId])) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }
}
