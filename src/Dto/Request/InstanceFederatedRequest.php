<?php

namespace App\Dto\Request;

final readonly class InstanceFederatedRequest
{
    public function __construct(
        public string $instance,
    ) {
    }
}
