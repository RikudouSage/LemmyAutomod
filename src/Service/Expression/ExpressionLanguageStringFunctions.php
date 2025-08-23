<?php

namespace App\Service\Expression;

use Rikudou\Iterables\Iterables;
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
            new ExpressionFunction(
                'matches_any',
                $this->uncompilableFunction(),
                $this->matchesAny(...),
            )
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

    /**
     * @param iterable<string> $regexes
     */
    private function matchesAny(array $context, string $string, iterable $regexes): bool
    {
        return Iterables::any(
            $regexes,
            fn (string $regex): bool => preg_match(
                '#' . str_replace('#', '\\#', $regex) . '#',
                $string,
            ),
        );
    }
}
