<?php

namespace App\Service\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final readonly class ExpressionLanguageStringFunctions extends AbstractExpressionLanguageFunctionProvider
{
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'to_lower',
                $this->uncompilableFunction(),
                $this->toLower(...),
            )
        ];
    }

    private function toLower(array $context, string $string): string
    {
        return mb_strtolower($string);
    }
}
