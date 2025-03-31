<?php

namespace App\Service\Expression;

use LogicException;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final readonly class ExpressionLanguageArrayFunctions extends AbstractExpressionLanguageFunctionProvider
{
    public function __construct(
        private ExpressionLanguage $expressionLanguage,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'each',
                $this->uncompilableFunction(),
                $this->each(...),
            ),
        ];
    }

    private function each(array $context, string $iterableExpression, string ...$callableExpressions): mixed
    {
        $iterable = $this->expressionLanguage->evaluate($iterableExpression, $context);
        if (!is_iterable($iterable)) {
            throw new LogicException("The expression '{$iterableExpression}' does not evaluate to an iterable type. Got: " . get_debug_type($iterable));
        }

        $result = null;
        foreach ($iterable as $key => $value) {
            foreach ($callableExpressions as $callableExpression) {
                $context['value'] = $value;
                $context['key'] = $key;

                $result = $this->expressionLanguage->evaluate($callableExpression, $context);
            }
        }

        return $result;
    }
}
