<?php

namespace App\Service\AiHorde\MessageFormatter;

use App\Enum\AiActor;
use App\Enum\AiModel;
use App\Service\AiHorde\Message\Message;
use App\Service\AiHorde\Message\MessageHistory;

final readonly class ChatMLPromptFormat implements MessageFormatter
{
    public function getPrompt(MessageHistory $messages): string
    {
        return trim(implode("\n", array_map(function (Message $message) {
            return "<|im_start|>{$message->role->value}\n{$message->content}<|im_end|>";
        }, [...$messages])));
    }

    public function formatOutput(string $message): Message
    {
        $role = 'assistant';
        $message = trim($message);

        if (str_starts_with($message, '<|im_start|>')) {
            $message = substr($message, strlen('<|im_start|>'));
            $parts = explode("\n", $message, 2);
            $message = $parts[1];
            $role = $parts[0];
        }
        if (str_ends_with($message, '<|im_end|>')) {
            $message = substr($message, 0, -strlen('<|im_end|>'));
        }

        $role = AiActor::tryFrom($role) ?? AiActor::Assistant;

        return new Message(role: $role, content: $message);
    }

    public function supports(AiModel $model): bool
    {
        return in_array($model, [AiModel::Mistral7BOpenHermes], true);
    }
}
