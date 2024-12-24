<?php

namespace App\Service\AiHorde\MessageFormatter;

use App\Enum\AiActor;
use App\Enum\AiModel;
use App\Service\AiHorde\Message\Message;
use App\Service\AiHorde\Message\MessageHistory;
use RuntimeException;

final readonly class LlamaPromptFormatter implements MessageFormatter
{
    public function getPrompt(MessageHistory $messages): string
    {
        $result = '<|begin_of_text|>';

        foreach ($messages as $message) {
            $result .= "<|start_header_id|>{$message->role->value}<|end_header_id|>\n\n";
            $result .= $message->content;
            $result .= '<|eot_id|>';
        }

        return $result;
    }

    public function formatOutput(string $message): Message
    {
        $regex = /** @lang RegExp */ '@(.+?)\s+(.*)@';
        if (!preg_match($regex, $message, $matches)) {
            throw new RuntimeException('Response did not match the expected output');
        }

        return new Message(role: AiActor::from(trim($matches[1])), content: trim($matches[2]));
    }

    public function supports(AiModel $model): bool
    {
        return $model === AiModel::Llama318BInstruct;
    }
}
