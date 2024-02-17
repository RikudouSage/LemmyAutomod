<?php

namespace App\Context;

final class Context
{
    /**
     * @var array<string>
     */
    private array $messages = [];

    public function addMessage(string $message): int
    {
        $this->messages[] = $message;

        return array_key_last($this->messages);
    }

    /**
     * @return array<string>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
