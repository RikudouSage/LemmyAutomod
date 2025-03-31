<?php

namespace App\Service\Expression;

use App\Helper\Markdown\MarkdownDocument;
use League\CommonMark\CommonMarkConverter;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final readonly class ExpressionLanguageMarkdownFunctions extends AbstractExpressionLanguageFunctionProvider
{
    public function __construct(
        private CommonMarkConverter $markdownConverter,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'markdown',
                $this->uncompilableFunction(),
                $this->toMarkdown(...),
            ),
        ];
    }

    private function toMarkdown(array $context, string $content): MarkdownDocument
    {
        return new MarkdownDocument($this->markdownConverter->convert($content)->getDocument());
    }
}
