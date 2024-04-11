<?php

namespace App\Service\Expression;

use Closure;
use LogicException;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final readonly class ExpressionLanguageStringFunctions implements ExpressionFunctionProviderInterface
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

    private function uncompilableFunction(): Closure
    {
        return fn() => throw new LogicException('This function cannot be compiled');
    }

    private function toLower(array $context, string $string): string
    {
        return mb_strtolower($string);
    }
}
