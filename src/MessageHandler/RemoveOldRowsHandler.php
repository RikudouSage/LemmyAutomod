<?php

namespace App\MessageHandler;

use App\Entity\RemovalLog;
use App\Message\RemoveOldRowsMessage;
use App\Repository\RemovalLogRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RemoveOldRowsHandler
{
    public function __construct(
        private RemovalLogRepository $repository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(RemoveOldRowsMessage $message): void
    {
        $messages = $this->repository->findOlderThan(new DateTimeImmutable());
        array_walk($messages, fn (RemovalLog $log) => $this->entityManager->remove($log));
        $this->entityManager->flush();
    }
}
