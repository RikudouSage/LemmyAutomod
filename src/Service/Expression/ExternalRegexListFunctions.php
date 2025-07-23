<?php

namespace App\Service\Expression;

use App\Service\ExternalRegexListManager;
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

    private function fetchExternalLists(array $context, string ...$listNames): array
    {
        $result = [];
        foreach ($listNames as $listName) {
            $result = [...$result, ...$this->externalRegexListManager->getList(
                $this->externalRegexListManager->findByName($listName),
            )];
        }

        return $result;
    }
}
