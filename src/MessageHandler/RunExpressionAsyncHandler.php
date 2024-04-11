<?php

namespace App\MessageHandler;

use App\Message\RunExpressionAsyncMessage;
use App\Service\Expression\ExpressionLanguage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RunExpressionAsyncHandler
{
    public function __construct(
        private ExpressionLanguage $expressionLanguage,
    ) {
    }

    public function __invoke(RunExpressionAsyncMessage $message): void
    {
        $this->expressionLanguage->evaluate($message->expression, $message->context);
    }
}
