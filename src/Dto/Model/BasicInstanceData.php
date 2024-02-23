<?php

namespace App\Dto\Model;

final readonly class BasicInstanceData
{
    public function __construct(
        public string $instance,
        public ?string $software,
        public ?string $version,
        public ?bool $openRegistrations,
    ) {
    }
}
