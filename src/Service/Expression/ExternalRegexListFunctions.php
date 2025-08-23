<?php

namespace App\Service\Expression;

use App\Service\ExternalRegexListManager;
use Rikudou\Iterables\RewindableGenerator;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final readonly class ExternalRegexListFunctions extends AbstractExpressionLanguageFunctionProvider
{
    public function __construct(
        private ExternalRegexListManager $externalRegexListManager,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                name: 'external_lists',
                compiler: $this->uncompilableFunction(),
                evaluator: $this->fetchExternalLists(...),
            )
        ];
    }

    private function fetchExternalLists(array $context, string ...$listNames): iterable
    {
        return new RewindableGenerator(function () use ($listNames) {
            foreach ($listNames as $listName) {
                yield from $this->externalRegexListManager->getList(
                    $this->externalRegexListManager->findByName($listName),
                );
            }
        });
    }
}
