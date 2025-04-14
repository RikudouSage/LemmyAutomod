<?php

namespace App\Dto\Request;

final readonly class VersionCheckRequest
{
    public function __construct(
        public string $uiVersion,
    ) {
    }
}
