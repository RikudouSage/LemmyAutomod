<?php

namespace App\MessageHandler;

use App\Automod\ModAction\Notification\NotifyOfActionTaken;
use App\Context\Context;
use App\Message\RunExpressionAsyncMessage;
use App\Service\Expression\ExpressionLanguage;
use App\Service\Expression\ExpressionLanguageNotifier;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RunExpressionAsyncHandler
{
    public function __construct(
        private ExpressionLanguage $expressionLanguage,
        private ExpressionLanguageNotifier $notifier,
        private NotifyOfActionTaken $notifyAction,
    ) {
    }

    public function __invoke(RunExpressionAsyncMessage $message): void
    {
        $this->expressionLanguage->evaluate($message->expression, $message->context);
        if ($this->notifyAction->shouldRun($message->context['object'])) {
            $this->notifyAction->takeAction($message->context['object'], $this->notifier->currentContext ?? new Context());
            // to avoid double messages in case it's running synchronously (e.g. when debugging)
            $this->notifier->currentContext = null;
        }
    }
}
