<?php

namespace App\Attribute;

use Attribute;
use JetBrains\PhpStorm\ExpectedValues;

/**
 * Describes parameters to use for LemmyWebhook, has no real use except documentation.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class WebhookConfig
{
    public function __construct(
        public ?string $bodyExpression,
        public ?string $filterExpression,
        public string $objectType,
        #[ExpectedValues(values: ['INSERT', 'UPDATE', 'DELETE'])]
        public string $operation,
        public ?string $enhancedFilter,
        public ?string $uniqueNameSuffix = null,
    ) {
    }
}
