<?php

namespace App\Service\AiHorde\MessageFormatter;

use App\Enum\AiModel;
use App\Service\AiHorde\Message\Message;
use App\Service\AiHorde\Message\MessageHistory;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.message_formatter')]
interface MessageFormatter
{
    public function getPrompt(MessageHistory $messages): string;

    public function formatOutput(string $message): Message;
    public function supports(AiModel $model): bool;
}
