<?php

namespace App\Helper\Markdown;

use Countable;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Node\Block\Document;
use Rikudou\Iterables\RewindableGenerator;
use Traversable;

final class MarkdownDocument
{
    public Traversable&Countable $images {
        get => new RewindableGenerator(function () {
            foreach ($this->document->iterator() as $node) {
                if ($node instanceof Image) {
                    yield $node;
                }
            }
        });
    }

    public function __construct(
        private readonly Document $document,
    ) {
    }
}
