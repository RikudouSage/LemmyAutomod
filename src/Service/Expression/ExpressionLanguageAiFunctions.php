<?php

namespace App\Service\Expression;

use App\Enum\AiActor;
use App\Enum\AiModel;
use App\Service\AiHorde\AiHorde;
use App\Service\AiHorde\Message\Message;
use App\Service\AiHorde\Message\MessageHistory;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final readonly class ExpressionLanguageAiFunctions extends AbstractExpressionLanguageFunctionProvider
{
    public function __construct(
        private AiHorde $aiHorde,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'ai_analyze',
                $this->uncompilableFunction(),
                $this->aiAnalyzeFunction(...),
            ),
        ];
    }

    private function aiAnalyzeFunction(array $context, string $message, ?string $systemPrompt = null): string
    {
        $history = new MessageHistory();
        if ($systemPrompt !== null) {
            $history[] = new Message(role: AiActor::System, content: $systemPrompt);
        }

        return $this->aiHorde->getResponse($message, AiModel::Mistral7BOpenHermes, $history);
    }
}
