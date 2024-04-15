<?php

namespace App\Service\AiHorde\Message;

use App\Enum\AiActor;
use JsonSerializable;
use Stringable;

final class Message implements JsonSerializable, Stringable
{
    public function __construct(
        public AiActor $role,
        public string  $content,
    ) {
    }

    /**
     * @return array{role: string, content: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'role' => $this->role->value,
            'content' => $this->content,
        ];
    }

    public function __toString()
    {
        return $this->content;
    }
}
