<?php

namespace App\MessageHandler;

use App\Entity\RemovalLog;
use App\Message\RemovePostMessage;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RemovePostHandler
{
    public function __construct(
        private LemmyApi $api,
        private ?DateInterval $removalLogValidity,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(RemovePostMessage $message): void
    {
        $this->api->moderator()->removePost(post: $message->postId);
        if ($this->removalLogValidity) {
            $log = (new RemovalLog())
                ->setType('post')
                ->setTargetId($message->postId)
                ->setValidUntil((new DateTimeImmutable())->add($this->removalLogValidity))
            ;
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        }
    }
}
