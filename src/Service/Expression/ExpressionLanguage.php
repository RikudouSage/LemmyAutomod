<?php

namespace App\Service\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage as SymfonyExpressionLanguage;

final class ExpressionLanguage extends SymfonyExpressionLanguage
{
    protected function registerFunctions(): void
    {
        // intentionally empty
    }
}
