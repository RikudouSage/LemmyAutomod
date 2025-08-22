<?php

namespace App\Message;

use DateInterval;
use Rikudou\LemmyApi\Response\Model\Person;

final readonly class BanUserMessage
{
    public function __construct(
        public Person $user,
        public string $reason,
        public bool $removePosts,
        public bool $removeComments,
        public ?DateInterval $duration = null,
    ) {
    }
}
