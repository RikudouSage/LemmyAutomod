<?php

namespace App\Attribute;

use Attribute;

/**
 * Describes parameters to use for LemmyWebhook, has no real use except documentation.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class WebhookConfig
{
    public function __construct(
        public ?string $bodyExpression,
        public ?string $filterExpression,
        public string $objectType,
        public string $operation,
        public ?string $enhancedFilter,
    ) {
    }
}
