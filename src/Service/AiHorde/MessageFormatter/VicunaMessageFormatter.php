<?php

namespace App\Service\AiHorde\MessageFormatter;

use App\Enum\AiActor;
use App\Enum\AiModel;
use App\Service\AiHorde\Message\Message;
use App\Service\AiHorde\Message\MessageHistory;

final readonly class VicunaMessageFormatter implements MessageFormatter
{
    public function getPrompt(MessageHistory $messages): string
    {
        $result = '';
        foreach ($messages as $message) {
            $result .= "\n" . strtoupper($message->role->value) . ': ' . $message->content;
        }
        $result .= "\nASSISTANT:";

        return $result;
    }

    public function formatOutput(string $message): Message
    {
        return new Message(
            role: AiActor::Assistant,
            content: trim($message),
        );
    }

    public function supports(AiModel $model): bool
    {
        return in_array($model, [AiModel::Fimbulvetr11Bv2], true);
    }
}
