<?php

namespace App\Service\Expression;

use Closure;
use LogicException;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

abstract readonly class AbstractExpressionLanguageFunctionProvider implements ExpressionFunctionProviderInterface
{
    protected function uncompilableFunction(): Closure
    {
        return fn () => throw new LogicException('This function cannot be compiled');
    }
}
