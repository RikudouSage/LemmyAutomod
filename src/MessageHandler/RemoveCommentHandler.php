<?php

namespace App\MessageHandler;

use App\Entity\RemovalLog;
use App\Message\RemoveCommentMessage;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RemoveCommentHandler
{
    public function __construct(
        private LemmyApi $api,
        private ?DateInterval $removalLogValidity,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(RemoveCommentMessage $message): void
    {
        $this->api->moderator()->removeComment(comment: $message->commentId);
        if ($this->removalLogValidity) {
            $log = (new RemovalLog())
                ->setType('comment')
                ->setTargetId($message->commentId)
                ->setValidUntil((new DateTimeImmutable())->add($this->removalLogValidity))
            ;
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        }
    }
}
