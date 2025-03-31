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
            ),
            new ExpressionFunction(
                'http_host',
                $this->uncompilableFunction(),
                $this->httpHost(...),
            ),
        ];
    }

    private function toLower(array $context, string $string): string
    {
        return mb_strtolower($string);
    }

    private function httpHost(array $context, string $url): string
    {
        return parse_url($url, PHP_URL_HOST);
    }
}
