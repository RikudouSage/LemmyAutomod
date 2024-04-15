<?php

namespace App\Service\AiHorde\MessageFormatter;

use App\Enum\AiActor;
use App\Enum\AiModel;
use App\Service\AiHorde\Message\Message;
use App\Service\AiHorde\Message\MessageHistory;

final readonly class AlpacaMessageFormatter implements MessageFormatter
{
    public function getPrompt(MessageHistory $messages): string
    {
        $result = '';
        foreach ($messages as $message) {
            if ($message->role === AiActor::System) {
                $result .= $message->content . "\n\n";
            }
            if ($message->role === AiActor::User) {
                $result .= "### Instruction:\n{$message->content}\n\n";
            }
            if ($message->role === AiActor::Assistant) {
                $result .= "### Response:\n{$message->content}\n\n";
            }
        }

        $result .= "### Response:";

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
        return in_array($model, [AiModel::LLaMA213BEstopia], true);
    }
}
