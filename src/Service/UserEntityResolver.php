<?php

namespace App\Service;

use App\Entity\ResolvableUserEntity;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Rikudou\LemmyApi\LemmyApi;

final readonly class UserEntityResolver
{
    public function __construct(
        private LemmyApi $api,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function resolve(ResolvableUserEntity ...$users): void
    {
        foreach ($users as $user) {
            if ($user->getUserId()) {
                continue;
            }

            if (!$user->getUsername() || !$user->getInstance()) {
                throw new LogicException("Both username and instance are needed to fetch the user's ID.");
            }

            $person = $this->api->user()->get("{$user->getUsername()}@{$user->getInstance()}");
            $user->setUserId($person->id);
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();
    }
}
