<?php

namespace App\Service;

use App\Entity\IgnoredUser;
use App\Repository\IgnoredUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Person;

final readonly class IgnoredUserManager
{
    public function __construct(
        private IgnoredUserRepository $repository,
        private EntityManagerInterface $entityManager,
        private UserEntityResolver $userEntityResolver,
    ) {
    }

    public function shouldBeIgnored(Person $person): bool
    {
        return in_array($person->id, $this->getIgnoredUserIds(), true);
    }

    /**
     * @return array<int>
     */
    private function getIgnoredUserIds(): array
    {
        try {
            return array_map(function (IgnoredUser $user): int {
                $this->userEntityResolver->resolve($user);
                return $user->getUserId();
            }, $this->repository->findBy(['enabled' => true]));
        } finally {
            $this->entityManager->flush();
        }
    }
}
